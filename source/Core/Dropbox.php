<?php

    namespace BBDropbox\Core;
    use BBCurl\Core\Request;

    /**
     * Class Dropbox | Algumas Rotas Importantes para Obtenção de Arquivos e Pastas.
     * @author Carlos Mateus Carvalho <carvalho.ti.adm@gmail.com>
     * @package BBDropbox\Core
     */
    class Dropbox{

        /**
         * @var Token - Proriedade onde será armazanada a Token do tipo Bearer.
         */
        private $token;

        /**
         * @var MemberID - Chave Secreta obitada pela função memberList do tipo dbmid:
         */
        private $userToken;

        /**
         * Método responsável por guardar a autorização das requisições da api
         * @param string $token - Token tipo Bearer
         * @param string|null $userToken - Member id obtido da função memberList() fo tipo dbmid;
         * @return $this
         */
        public function auth(string $token, string $userToken=null)
        {
            $this->token = $token;
            $this->userToken = $userToken;
            return $this;
        }

        /**
         * Método responsável por por trazer a lista de membros do dropbox
         * @return bool|array
         * @throws \Exception
         */
        public function membersList(){
            if (bb_session_request_limit("membersList", 3, 60*5)) {
                return true;
            }
            return (new Request(URI_MEMBERS_LIST))->withJson()
                ->setHeader("Authorization", $this->token)
                ->post(['limit' => 100, 'include_removed' => false])->run()->data();
        }

        /**
         * Método responsável por trazer a informação de um membro especifico.
         * @return bool|array
         * @throws \Exception
         */
        public function memberInfo()
        {
            if (bb_session_request_limit("memberInfo", 3, 60*5)) {
               return true;
            }

           return (new Request(URI_MEMBER_INFO))->withJson()
                ->setHeader("Authorization", $this->token)
                ->post([
                    "members" => [
                        [".tag" => "team_member_id",
                        "team_member_id" => $this->userToken]
                    ]
                ])->run()->data();
        }

        /**
         * Método responsável por listar as pastas dos arquivos da dropbox
         * @param $path - Pasta onde está localizada na cloud.
         * @return array|bool
         * @throws \Exception
         */
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

        /**
         * Método responsável por baixar os arquivos da cloud.
         * @param string $id - Id do arquivo ou pasta do arquivo
         * @param string $dest - Diretorio da pasta onde será armazenada o arquivo.
         * @return bool
         * @throws \Exception
         */
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


        /**
         * Método responsável por baixar um arquivo ou basta em zip, com possibilidade de extração do zip.
         * @param string $cloud - Pasta ou arquivo onde está localizado na nuvem. ex.: /arquivos
         * @param string $disk - Diretorio onde será armazenado o zip no disco seguido pelo nome, ex. __DIR__ . '/path/to/file.zip'
         * @param bool $descompactar - Caso verdadeiro irá descompactar o arquivo na pasta informada acima.
         * @param string|null $folder - Caso queria descompactar em uma pasta adicional ao parametro disk.
         * @return bool
         * @throws \Exception
         */
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

        /**
         * Método responsável por fazer o Upload do arquivo no dropbox.
         * @param string $disk - Onde está localizado o arquivo refente no disco seguido pelo nome ex.: /path/to/file.pdf
         * @param string $cloud - Onde será armazenado o arquivo na cloud seguido pelo nome ex.: /path/to/file.pdf;
         * @return bool
         */
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

        /**
         * Método responsável por por deletar um arquivo na nuvem
         * @param string $path - Caminho da nuvem a ser deletado seguido pelo nome do arquivo ex.: /path/to/file.pdf
         * @return bool
         * @throws \Exception
         */
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

        /**
         * Método responsável por exportar arquivos no navegador
         * @param $path - Caminho na nuvem seguido pelo nome do arquivo. ex.: /path/to/file.xls
         * @return bool
         * @throws \Exception
         */
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

        /**
         * Método responsável por procurar um arquivo ou pasta na cloud.
         * @param $query - Objeto da busca.
         * @param $path - Onde Pesquisar na cloud ex.: /arquivos
         * @param int $max - Máximo de resultados.
         * @return array|bool
         * @throws \Exception
         */
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