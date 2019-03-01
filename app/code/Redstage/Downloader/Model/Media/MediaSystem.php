<?php

namespace Redstage\Downloader\Model\Media;

class MediaSystem
{
    const MEDIA = 'media';
    
    private $_directory;

    function __construct(){
        $dateTime = new \DateTime("now");
        $time =  $dateTime->format('Y_m_d_H_i_s');
        $this->_directory = sprintf("import_%s", $time);
        $this->createPath();
    }
    
    private function createPath(){
        if (!file_exists(self::MEDIA.$this->_directory)) {
            mkdir(self::MEDIA.DIRECTORY_SEPARATOR.$this->_directory, 0777, true);
        }
    }

    public function getPath(){
        return self::MEDIA.DIRECTORY_SEPARATOR.$this->_directory;
    }
}