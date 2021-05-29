<?php
session_start();
header('Location: /' . (isset($_SESSION['user']) ? 'list/user' : 'login') . '/');
