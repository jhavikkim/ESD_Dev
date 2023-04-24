<?php
function testAudio(){
  return shell_exec("/usr/local/bin/python3 /Users/jamestseng/test_audio.py > /dev/null & echo $!");
}

function testTouch(){
  return shell_exec("/usr/local/bin/python3 /Users/jamestseng/test_touch.py");
}
?>