<?php

/**
 * @file
 * A Phing task to run Yiic commands.
 */
require_once "phing/Task.php";

class YiicParam {

  private $value;

  public function addText($str) {
    $this->value = $str;
  }

  public function getValue() {
    return $this->value;
  }

}

class YiicOption {

  private $name;
  private $value;

  public function setName($str) {
    $this->name = $str;
  }

  public function getName() {
    return $this->name;
  }

  public function addText($str) {
    $this->value = $str;
  }

  public function getValue() {
    return $this->value;
  }

  public function toString() {
    $name  = $this->getName();
    $value = $this->getValue();
    $str = '--'.$name;
    if (!empty($value)) {
      $str .= '='.$value;
    }
    return $str;
  }

}

class YiicTask extends Task {

  /**
   * The message passed in the buildfile.
   */
  private $command = array();
  private $bin = NULL;
  private $dir = NULL;
  private $options = array();
  private $params = array();
  private $return_glue = "\n";
  private $return_property = NULL;
  private $haltonerror = TRUE;

  /**
   * The Yiic command to run.
   */
  public function setCommand($str) {
    $this->command = $str;
  }

  /**
   * Path the Yiic executable.
   */
  public function setBin($str) {
    $this->bin = $str;
  }

  /**
   * Drupal root directory to use.
   */
  public function setDir($str) {
    $this->dir = $str;
  }


  /**
   * The 'glue' characters used between each line of the returned output.
   */
  public function setReturnGlue($str) {
    $this->return_glue = (string) $str;
  }

  /**
   * The name of a Phing property to assign the Yiic command's output to.
   */
  public function setReturnProperty($str) {
    $this->return_property = $str;
  }

  /**
   * The name of a Phing property to assign the Yiic command's output to.
   */
  public function setHaltonerror($var) {
    if (is_string($var)) {
      $var = strtolower($var);
      $this->haltonerror = ($var === 'yes' || $var === 'true');
    } else {
      $this->haltonerror = !!$var;
    }
  }

  /**
   * Parameters for the Yiic command.
   */
  public function createParam() {
    $o = new YiicParam();
    $this->params[] = $o;
    return $o;
  }

  /**
   * Options for the Yiic command.
   */
  public function createOption() {
    $o = new YiicOption();
    $this->options[] = $o;
    return $o;
  }

  /**
   * Initialize the task.
   */
  public function init() {
    // Get default dir and binary from project.
    $this->dir = $this->getProject()->getProperty('yiic.dir');
    $this->bin = $this->getProject()->getProperty('yiic.bin');
  }

  /**
   * The main entry point method.
   */
  public function main() {
    $command = array();

    $command[] = !empty($this->bin) ? $this->bin : 'yiic';

    $this->options[] = array();

    if (empty($this->dir)) {
      $this->dir = realpath(".").DIRECTORY_SEPARATOR;
    }else{
      $this->dir = realpath($this->dir).DIRECTORY_SEPARATOR;
    }

    $command[] = $this->command;
    
    if ($this->action) {
      $command[] = $this->action;
    }
    
    foreach ($this->options as $option) {
      $command[] = $option->toString();
    }

    foreach ($this->params as $param) {
      $command[] = $param->getValue();
    }

    $command = implode(' ', $command);

    // Execute Yiic.
    $this->log("Executing '$command'...");
    $output = array();
    exec($this->dir.$command, $output, $return);
    // Collect Yiic output for display through Phing's log.
    foreach ($output as $line) {
      $this->log($line);
    }
    // Set value of the 'pipe' property.
    if (!empty($this->return_property)) {
      $this->getProject()->setProperty($this->return_property, implode($this->return_glue, $output));
    }
    // Build fail.
    if ($this->haltonerror && $return != 0) {
      throw new BuildException("Yiic exited with code $return");
    }
    return $return != 0;
  }

}

