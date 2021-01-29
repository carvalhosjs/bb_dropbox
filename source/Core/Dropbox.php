<?php

    namespace BBDropbox\Core;
    use BBCurl\Core\Request;

    class Dropbox extends Request{

        public function __construct(string $uri)
        {
            parent::__construct($uri);
        }

        public function listFolder($path)
        {
            if (bb_session_request_limit("listFolder", 3, 60*5)) {
                return true;
            }
            $data = $this
                ->withJson()
                ->setHeader("Authorization", DROPBOX_API_TOKEN)
                ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                ->post(
                ["path" => $path]
            )->run()->data();

            return empty($data) ? [] : $data;

        }

        public function downloadFile(string $id, string $dest)
        {
            if (bb_session_request_limit("downloadFile", 3, 60*5)) {
                return true;
            }
            $this
                ->post()
                ->setHeader("Authorization", DROPBOX_API_TOKEN)
                ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                ->setJsonHeader("Dropbox-API-Arg", ['path' => $id])
                ->download($dest);
            return true;
        }

        public function downloadZip(string $path, string $dest, bool $descompactar=false, string $folder=null){
            $mydest = explode("/", $dest);
            array_pop($mydest);
            $folder = empty($folder) ? '' : $folder . '/';
            $mydest = implode("/", $mydest) . '/' . $folder;

            if (bb_session_request_limit("downloadZip", 3, 60*5)) {
                return true;
            }
            $this
                ->post()
                ->setHeader("Authorization", DROPBOX_API_TOKEN)
                ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                ->setJsonHeader("Dropbox-API-Arg", ['path' => $path])
                ->download($dest);

                if($descompactar){
                        $zip = new \ZipArchive;
                        if ($zip->open($dest) === TRUE) {
                            $zip->extractTo($mydest);
                            $zip->close();
                            unlink($dest);
                            return true;
                        } else {
                            return false;
                    }
                }
                return true;
        }

        public function upload(string $disk, string $cloud){

            if (bb_session_request_limit("upload", 3, 60*5)) {
                return true;
            }

             $this
                ->sendFile($disk)
                ->setHeader("Authorization", DROPBOX_API_TOKEN)
                ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                ->setHeader("Content-Type", 'application/octet-stream')
                ->setJsonHeader("Dropbox-API-Arg", ["path" => $cloud, "mode" => "overwrite",  "autorename" => true, "mute" => false, 'strict_conflict' => false])
                ->run();
             return true;
        }


        public function deleteFile(string $path)
        {
            if (bb_session_request_limit("deleteFile", 3, 60*5)) {
                return true;
            }

            $this
                ->post(['path' => $path])
                ->withJson()
                ->setHeader("Authorization", DROPBOX_API_TOKEN)
                ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                ->run();
            return true;
        }

        public function exportFile($path)
        {
            if (bb_session_request_limit("export", 3, 60*5)) {
                return true;
            }

             $this
                ->post()
                ->setJsonHeader("Dropbox-API-Arg", ["path" => $path])
                ->setHeader("Authorization", DROPBOX_API_TOKEN)
                ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                ->run()->withErrors();
            return true;

        }

        public function searchFileFolder($query, $path, $max=20){

            if (bb_session_request_limit("searchFileFolder", 3, 60*5)) {
                return true;
            }
            $result = $this
                    ->post([
                        "query" => $query,
                        "options" => [
                            "path" => $path,
                            "max_results" => $max,
                            "file_status" => "active",
                            "filename_only" => false
                        ],
                        "match_field_options" => [
                            "include_highlights" => false
                        ]
                    ])
                    ->withJson()
                    ->setHeader("Authorization", DROPBOX_API_TOKEN)
                    ->setHeader("Dropbox-API-Select-User", DROPBOX_API_SELECT_USER)
                    ->run()->data()['matches'];



            if(!empty($result)){
                return $result;
            }else{
                return [];
            }
        }



    }