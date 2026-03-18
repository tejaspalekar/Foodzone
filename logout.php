<?php
require_once 'db.php';
session_destroy();
redirect('login.php');
