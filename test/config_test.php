<?php

require (dirname(__FILE__) . "/../config_default.php");
$config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";

$config['cops_mail_configuration'] = array( "smtp.host"     => "smtp.free.fr",
                                                "smtp.username" => "",
                                                "smtp.password" => "",
                                                "smtp.secure"   => "",
                                                "address.from"  => "cops@slucas.fr"
                                                );
