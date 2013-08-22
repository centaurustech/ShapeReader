<?php

namespace muka\ShapeReader;

class DbfFile {

    private $filename;
    private $data;
    private $record_number = 0;

    public function __construct($shpFilename) {
        $this->filename = $this->getFilename($shpFilename);
    }

    public function getFilename($filename) {

        if($this->filename) {
            return $this->filename;
        }

        if (!strstr($filename, ".")) {
            $filename .= ".dbf";
        }

        if (substr($filename, strlen($filename) - 3, 3) != "dbf") {
            $filename = substr($filename, 0, strlen($filename) - 3) . "dbf";
        }

        return $filename;
    }

    public function getData() {

        if(!$this->data) {
            $this->load();
        }

        return $this->data;
    }

    public function setData(array $row) {

        $this->open(true);
        unset($row["deleted"]);

        if (!dbase_replace_record($this->dbf, array_values($row), $this->record_number)) {
            throw new Exception\DbfException("Error writing data to file.");
        } else {
            $this->data = $row;
        }

        $this->close();
    }

    private function open($check_writeable = false) {
        $check_function = $check_writeable ? "is_writable" : "is_readable";
        if ($check_function($this->filename)) {
            $this->dbf = dbase_open($this->filename, ($check_writeable ? 2 : 0));
            if (!$this->dbf) {
                throw new Exception\DbfException(sprintf("Error loading %s", $this->filename));
            }
        } else {
            throw new Exception\DbfException(sprintf("File doesn't exists (%s)", $this->filename));
        }
    }

    public function __destruct() {
        $this->close();
    }

    private function close() {
        if ($this->dbf) {
            dbase_close($this->dbf);
            $this->dbf = null;
        }
    }

    private function load() {
        $this->open();
        $this->data = dbase_get_record_with_names($this->dbf, $this->record_number);
        $this->close();
    }

}