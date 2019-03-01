<?php
namespace Redstage\Downloader\Model\Csv;

class CsvParser
{
    private $_csvFile;

    private $_delimiter;

    function __construct(string $csvFile, $delimiter){
        if(!file_exists($csvFile)){
             throw new \Exception('file not found');
        }
        $this->_csvFile = $csvFile;
        $this->_delimiter = $delimiter;
        $this->_items = [];
    }

    public function removeDuplicateFromColumn($column){
        $handle = $this->getHandle();
        $row = 1;
        $header = fgetcsv($handle, 1000, $this->_delimiter);
        if(!$this->validateColumn($header,$column)){
            throw new \Exception("Not a valid Column: $column");
        }
        $columnIndex = $this->getColumnIndex($header, $column);
        while(($data = fgetcsv($handle, 1000, $this->_delimiter))){
            if($this->isDuplicateItem($data, $columnIndex)){
                continue;
            }
            $this->_items[] = $data[$columnIndex];
        }
        $this->closeFile($handle);
        return $this->_items;
    }

    private function validateColumn($header, $column){
        if(in_array($column, $header)){
            return true;
        }
    }

    private function getColumnIndex($header, $column){
        $index = array_keys($header, $column);    
        return $index[0];
    }

    private function isDuplicateItem($data, $index){
        if(in_array($data[$index], $this->_items)){
            return true;
        }
        return false;
    }

    private function getHandle(){
        return fopen($this->_csvFile, "r");
    }

    private function closeFile($handle){
        fclose($handle);
    }

}