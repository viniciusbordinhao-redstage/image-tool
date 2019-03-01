<?php

namespace Redstage\Downloader\Model\Download;

use Redstage\Downloader\Model\Media\MediaSystem;
use Monolog\Logger;

class Downloader
{
    private $_mediaSystem;

    private $_logger;
    
    function __construct(MediaSystem $_mediaSystem, Logger $logger ){
        $this->_mediaSystem = $_mediaSystem;
        $this->_logger = $logger;
    }

    public function download($item){
        try{
            $raw = $this->downloadItem($item);
            $fileName = $this->getFileName($item);
            $handle = $this->openFile($fileName);
            $this->writeContent($handle, $raw);
            $this->closeFile($handle);
            $response = ["status" => "Success", "message" => "Downloaded"];
        } catch (\Exception $e){
            $response = ["status" => "Error", "message" => $e->getMessage()];
            $text = sprintf('Item: %s  not downloaded. reason: %s', $item, $e->getMessage());
            $this->_logger->debug($text);
        }
        return $response;
    }

    private function downloadItem($item){
        $ch = curl_init($item);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response=curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);
        if($contentType == 'text/html'){
            throw new \Exception("Not an Image: $httpcode $item");
        }
        if($httpcode != "200"){
            throw new \Exception("Image not downloaded: $httpcode $item");
        }
        return $body;
    }

    private function writeContent($handle, $raw){
        fwrite($handle, $raw);
    }

    private function getFileName($item){
        $fileName = basename($item);
        if($this->isFileExists($fileName)){
            $id = md5(uniqid(rand(), true));
            $fileName = preg_replace('/(.{4,4})$/', $id."$1", $fileName);
        }
        return $fileName;
    }

    private function openFile($fileName){
        return fopen($this->_mediaSystem->getPath().DIRECTORY_SEPARATOR.$fileName, 'wb');
    }

    private function closeFile($handle){
        fclose($handle);
        return;
    }

    private function isFileExists($fileName){
        if(file_exists($this->_mediaSystem->getPath().DIRECTORY_SEPARATOR.$fileName)){
            return true;
        }
        return false;
    }


}