Phing Yiic Task
--------------------------
A Yii console command task for Phing[1]. This task enable usage of Yii console commands in Phing build scripts.

Phing provides tools for usual tasks for PHP projects (phplint, jslint, VCS checkouts, files copy or merge, packaging, upload, etc.). Integration of Yiic in Phing is particularly useful when building and testing Yii projects in a continuous integration server such as Jenkins[2].
 
Installation and Usage
----------------------------------
To use the yiic task in your build file,  it must be made available to Phing so that the buildfile parser is aware a correlating XML element and it's parameters.  This is done by adding a <taskdef> tak to your build file, something like (see Phing documentation[3] for more information on the <taskdef> task).

  <taskdef name="yiic" classname="YiicTask" />
  
Base Yiic options are mapped to attribute of the Yiic task. Parameters are wrapped in elements. Value of a parameter is defined by the text child of the element. Options are mapped to elements with a name attribute. Value of an option can either be in the value attribute of the element or as text child (like params).
yiic custom-command custom-action --option --option2=value --option3=${somevalue} param1
  <yiic command="custom-command" action="custom-action">
    <option name="option">true</option>
    <option name="option2">value</option>
    <option name="option3" value="${somevalue}" />
    <param>param1</param>
  </yiic> 

[1] http://www.phing.info/
[2] http://jenkins-ci.org/
[3] http://www.phing.info/docs/guide/stable/chapters/appendixes/AppendixB-CoreTasks.html#TaskdefTask
