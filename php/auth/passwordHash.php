<?php

class passwordHash {
  public static function check_password($hash, $password) {
    return ($hash == $password);
  }
}

?>