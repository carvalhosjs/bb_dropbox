<?php

    namespace BBDropbox\Core;
    use BBCurl\Core\Request;


    class Dropbox{

        private $token;
        private $userToken;

        public function auth(string $token, string $userToken=null)
        {
            $this->token = $token;
            $this->userToken = $userToken;
            return $this;
        }


        public function membersList(){
            if (bb_session_request_limit("membersList", 3, 60*5)) {
                return true;
            }
            return (new Request(URI_MEMBERS_LIST))->withJson()
                ->setHeader("Authorization", $this->token)
                ->post(['limit' => 100, 'include_removed' => false])->run()->data();
        }


        public function listFolder($path)
        {
            if (bb_session_request_limit("listFolder", 3, 60*5)) {
                return true;
            }
            $data = (new Request(URI_FOLDER_LIST))
                ->withJson()
                ->setHeader("Authorization", $this->token)
                ->setHeader("Dropbox-API-Select-User", $this->userToken)
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
            (new Request(URI_DOWNLOAD_FILE))
                ->post()
                ->setHeader("Authorization", $this->token)
                ->setHeader("Dropbox-API-Select-User", $this->userToken)
                ->setJsonHeader("Dropbox-API-Arg", ['path' => $id])
                ->download($dest);
            return true;
        }

        public function downloadZip(string $cloud, string $disk, bool $descompactar=false, string $folder=null){
            $mydest = explode("/", $disk);
            array_pop($mydest);
            $folder = empty($folder) ? '' : $folder . '/';
            $mydest = implode("/", $mydest) . '/' . $folder;

            if (bb_session_request_limit("downloadZip", 3, 60*5)) {
                return true;
            }
            (new Request(URI_DOWNLOAD_ZIP))
                ->post()
                ->setHeader("Authorization", $this->token)
                ->setHeader("Dropbox-API-Select-User", $this->userToken)
                ->setJsonHeader("Dropbox-API-Arg", ['path' => $cloud])
                ->download($disk);

                if($descompactar){
                        $zip = new \ZipArchive;
                        if ($zip->open($disk) === TRUE) {
                            $zip->extractTo($mydest);
                            $zip->close();
                            unlink($disk);
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
                ->setHeader("Authorization", $this->token)
                ->setHeader("Dropbox-API-Select-User", $this->userToken)
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

            (new Request(URI_UPLOAD_FILE))
                ->post(['path' => $path])
                ->withJson()
                ->setHeader("Authorization", $this->token)
                ->setHeader("Dropbox-API-Select-User", $this->userToken)
                ->run();
            return true;
        }

        public function exportFile($path)
        {
            if (bb_session_request_limit("export", 3, 60*5)) {
                return true;
            }

            (new Request(URI_EXPORT_FILE))
                ->post()
                ->setJsonHeader("Dropbox-API-Arg", ["path" => $path])
                ->setHeader("Authorization", $this->token)
                ->setHeader("Dropbox-API-Select-User", $this->userToken)
                ->run()->withErrors();
            return true;

        }

        public function searchFileFolder($query, $path, $max=20){

            if (bb_session_request_limit("searchFileFolder", 3, 60*5)) {
                return true;
            }
            $result = (new Request(URI_SEARCH))
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
                    ->setHeader("Authorization", $this->token)
                    ->setHeader("Dropbox-API-Select-User", $this->userToken)
                    ->run()->data()['matches'];



            if(!empty($result)){
                return $result;
            }else{
                return [];
            }
        }



    }