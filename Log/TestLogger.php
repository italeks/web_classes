<?php

namespace Log;

class TestLogger implements LoggerInterface
{

    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
          $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

   private function out($level, $message)
   {
        echo '<div style="width:800px; border:1px solid red" ><strong>' . $level . '!!!</strong>' . $message . '</div>';
   }
    public function emergency($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function alert($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function critical($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function error($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function warning($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function notice($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function info($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function debug($message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out(__FUNCTION__, $message);
    }


    public function log($level, $message, array $context = array())
    {
        $message = $this->interpolate($message, $context);
        $this->out($level, $message);
    }

}