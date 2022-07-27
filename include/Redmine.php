<?php

class Redmine
{
    public $rdUrl;
    private $rdToken;

    public function __construct()
    {
        $config = require 'config.php';
        $this->rdUrl = $config['rdUrl'];
        $this->rdToken = $config['rdToken'];
    }

    public function GetData($type)
    {
        return json_decode(file_get_contents($this->rdUrl . $type .'.json?&key=' . $this->rdToken));
    }

    public function GetDataParam($type, $param)
    {
        return json_decode(file_get_contents($this->rdUrl . $type .'.json?&key=' . $this->rdToken . $param));
    }

    public function GetIssue($id)
    {
        return json_decode(file_get_contents($this->rdUrl .'issues/' . $id . '.json?&key=' . $this->rdToken));
    }
}
