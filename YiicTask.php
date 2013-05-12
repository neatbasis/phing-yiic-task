<?php

/**
 * @file
 * A Phing task to run Yiic commands.
 */
require_once "phing/Task.php";

class YiicParam
{

  private $_value;

  public function addText($str) {
    $this->_value = $str;
  }

  public function getValue() {
    return $this->_value;
  }

}

class YiicOption
{

  private $_name;
  private $_value;

  public function setName($str) {
    $this->_name = $str;
  }

  public function getName() {
    return $this->_name;
  }

  public function addText($str) {
    $this->_value = $str;
  }

  public function getValue() {
    return $this->_value;
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

class YiicTask extends Task
{

  /**
   * The message passed in the buildfile.
   */
  private $_command = array();
  private $_action = NULL;
  private $_bin = NULL;
  private $_dir = NULL;
  private $_options = array();
  private $_params = array();
  private $_returnGlue = "\n";
  private $_returnProperty = NULL;
  private $_haltOnError = TRUE;

  /**
   * The Yiic command to run.
   */
  public function setCommand($str) {
    $this->_command = $str;
  }
  
  /**
   * The action for command.
   */
  public function setAction($str) {
    $this->_action = $str;
  }

  /**
   * Path the Yiic executable.
   */
  public function setBin($str) {
    $this->_bin = $str;
  }

  /**
   * Drupal root directory to use.
   */
  public function setDir($str) {
    $this->_dir = $str;
  }


  /**
   * The 'glue' characters used between each line of the returned output.
   */
  public function setReturnGlue($str) {
    $this->_returnGlue = (string) $str;
  }

  /**
   * The name of a Phing property to assign the Yiic command's output to.
   */
  public function setReturnProperty($str) {
    $this->_returnProperty = $str;
  }

  /**
   * The name of a Phing property to assign the Yiic command's output to.
   */
  public function setHaltonerror($var) {
    if (is_string($var)) {
      $var = strtolower($var);
      $this->_haltOnError = ($var === 'yes' || $var === 'true');
    } else {
      $this->_haltOnError = !!$var;
    }
  }

  /**
   * Parameters for the Yiic command.
   */
  public function createParam() {
    $o = new YiicParam();
    $this->_params[] = $o;
    return $o;
  }

  /**
   * Options for the Yiic command.
   */
  public function createOption() {
    $o = new YiicOption();
    $this->_options[] = $o;
    return $o;
  }

  /**
   * Initialize the task.
   */
  public function init() {
    // Get default dir and binary from project.
    $this->_dir = $this->getProject()->getProperty('yiic.dir');
    $this->_bin = $this->getProject()->getProperty('yiic.bin');
  }

  /**
   * The main entry point method.
   */
  public function main() {
    $command = array();

    $command[] = !empty($this->_bin) ? $this->_bin : 'yiic';

    $this->_options[] = array();

    if (empty($this->_dir)) {
      $this->_dir = realpath(".").DIRECTORY_SEPARATOR;
    } else {
      $this->_dir = realpath($this->_dir).DIRECTORY_SEPARATOR;
    }

    $command[] = $this->_command;
    
    if ($this->_action) {
      $command[] = $this->_action;
    }
    
    foreach ($this->_options as $option) {
    	if($option instanceof YiicOption)
    		$command[] = $option->toString();
    }

    foreach ($this->_params as $param) {
    	if($option instanceof YiicParam)
    		$command[] = $param->getValue();
    }

    $command = implode(' ', $command);

    // Execute Yiic.
    $this->log("Executing '$command'...");
    $output = array();
    exec($this->_dir.$command, $output, $return);
    // Collect Yiic output for display through Phing's log.
    foreach ($output as $line) {
      $this->log($line);
    }
    // Set value of the 'pipe' property.
    if (!empty($this->_returnProperty)) {
      $this->getProject()->setProperty(
          $this->_returnProperty, implode($this->_returnGlue, $output)
      );
    }
    // Build fail.
    if ($this->_haltOnError && $return != 0) {
      throw new BuildException("Yiic exited with code $return");
    }
    return $return != 0;
  }

}
