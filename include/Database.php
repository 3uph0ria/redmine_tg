<?php

class Database
{
    private $link;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * @return $this
     */
    private function connect()
    {
        $config = require 'config.php';
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbName'] . ';charset=' . $config['charset'];
        $this->link = new PDO($dsn, $config['userName'], $config['password']);

        return $this;
    }

    public function GetPermissions()
    {
        $chatWeekStatsAll = $this->link->query("SELECT * FROM `permissions`");
        while($chatWeekStatsRow = $chatWeekStatsAll->fetch(PDO::FETCH_ASSOC))
        {
            $chatWeekStats[] = $chatWeekStatsRow;
        }
        return $chatWeekStats;
    }

    public function GetLeader()
    {
        $chatWeekStatsAll = $this->link->query("SELECT * FROM `users` where `PermissionId` = 1");
        while($chatWeekStatsRow = $chatWeekStatsAll->fetch(PDO::FETCH_ASSOC))
        {
            $chatWeekStats[] = $chatWeekStatsRow;
        }
        return $chatWeekStats;
    }

    public function GetImg($UserId)
    {
        $chatWeekStatsAll = $this->link->query("SELECT * FROM `img` where `UserId` = $UserId");
        while($chatWeekStatsRow = $chatWeekStatsAll->fetch(PDO::FETCH_ASSOC))
        {
            $chatWeekStats[] = $chatWeekStatsRow;
        }
        return $chatWeekStats;
    }

    public function DelImg($UserId)
    {
        $botUser =  $this->link->prepare("delete from `img` where `UserId` = ?");
        $botUser->execute(array($UserId));
    }


    public function AddUser($Name, $PeerId)
    {
        $botUser =  $this->link->prepare("INSERT INTO `users` (`Name`, `PeerId`) VALUES (?, ?)");
        $botUser->execute(array($Name, $PeerId));
    }

    public function AddImg($UserId, $FileId, $FileName)
    {
        $botUser =  $this->link->prepare("INSERT INTO `img` (`UserId`, `FileId`, `FileName`) VALUES (?, ?, ?)");
        $botUser->execute(array($UserId, $FileId, $FileName));
    }

    public function GetUser($PeerId)
    {
        $botUser =  $this->link->query("SELECT * FROM `users` where `PeerId` = $PeerId");
        return $botUser->fetch(PDO::FETCH_ASSOC);
    }

    public function GetAuthor($AuthorId)
    {
        $botUser =  $this->link->query("SELECT * FROM `users` where `AuthorId` = $AuthorId");
        return $botUser->fetch(PDO::FETCH_ASSOC);
    }

    public function UpdatePermission($IdPermission, $IdUser)
    {
        $botUser =  $this->link->prepare("Update `users` set `PermissionId` = ? where `Id` = ?");
        $botUser->execute(array($IdPermission, $IdUser));
    }

    public function UpdateUserFullName($Fullname, $IdUser)
    {
        $botUser =  $this->link->prepare("Update `users` set `FullName` = ? where `Id` = ?");
        $botUser->execute(array($Fullname, $IdUser));
    }

    public function UpdateActiveIssue($IdIssue, $IdUser)
    {
        $botUser =  $this->link->prepare("Update `users` set `ActiveIssue` = ? where `PeerId` = ?");
        $botUser->execute(array($IdIssue, $IdUser));
    }

    public function UpdateUserText($text, $IdUser)
    {
        $botUser =  $this->link->prepare("Update `users` set `Text` = ? where `PeerId` = ?");
        $botUser->execute(array($text, $IdUser));
    }

    public function UpdateUserProject($IdProject, $IdUser)
    {
        $botUser =  $this->link->prepare("Update `users` set `ProjectId` = ? where `PeerId` = ?");
        $botUser->execute(array($IdProject, $IdUser));
    }

    public function UpdateImgName($Id, $Name)
    {
        $botUser =  $this->link->prepare("Update `img` set `FileName` = ? where `Id` = ?");
        $botUser->execute(array($Name, $Id));
    }
}
