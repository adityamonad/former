<?php
namespace Former\Framework;

use Former\Interfaces\FrameworkInterface;
use Former\Traits\Field;
use Former\Traits\Framework;
use HtmlObject\Element;
use Illuminate\Container\Container;
use Underscore\Methods\ArraysMethods as Arrays;
use Underscore\Methods\StringMethods as String;

/**
 * The Twitter Bootstrap form framework
 */
class TwitterBootstrap3 extends Framework implements FrameworkInterface
{

  /**
   * The button types available
   * @var array
   */
  private $buttons = array(
    'large', 'small', 'mini', 'block',
    'danger', 'info', 'inverse', 'link', 'primary', 'success', 'warning'
  );

  /**
   * The field sizes available
   * @var array
   */
  private $fields = array(
    'col-sm-1', 'col-sm-2', 'col-sm-3', 'col-sm-4', 'col-sm-5', 'col-sm-6', 'col-sm-7',
    'col-sm-8', 'col-sm-9', 'col-sm-10', 'col-sm-11', 'col-sm-12'
  );

  /**
   * The field states available
   * @var array
   */
  protected $states = array(
    'success', 'warning', 'error', 'info',
  );

  /**
   * Create a new TwitterBootstrap instance
   *
   * @param \Illuminate\Container\Container $app
   */
  public function __construct(Container $app)
  {
    $this->app = $app;
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// FILTER ARRAYS //////////////////////////
  ////////////////////////////////////////////////////////////////////

  public function filterState($state)
  {
    // Filter out wrong states
    return in_array($state, $this->states) ? 'has-'.$state : null;
  }

  /**
   * Filter buttons classes
   *
   * @param  array $classes An array of classes
   * @return array A filtered array
   */
  public function filterButtonClasses($classes)
  {
    // Filter classes
    // $classes = array_intersect($classes, $this->buttons);

    $convertClasses = array(
      'large' => 'lg',
      'small' => 'sm',
      'mini' => 'xs',
    );

    $classes = Arrays::each($classes, function($class) use ($convertClasses) {
      return isset($convertClasses[$class]) ? $convertClasses[$class] : $class;
    });

    // Prepend button type
    $classes = $this->prependWith($classes, 'btn-');
    $classes[] = 'btn';

    return $classes;
  }

  /**
   * Filter field classes
   *
   * @param  array $classes An array of classes
   * @return array A filtered array
   */
  public function filterFieldClasses($classes)
  {
    $convertClasses = array(
      'xlarge' => 'col-sm-12',
      'large' => 'col-sm-9',
      'medium' => 'col-sm-6',
      'small' => 'col-sm-4',
      'xsmall' => 'col-sm-2',
    );

    $classes = Arrays::each($classes, function($class) use ($convertClasses) {
      return isset($convertClasses[$class]) ? $convertClasses[$class] : $class;
    });

    $classes = Arrays::each($classes, function($class) {
      return preg_replace('#^span#', 'col-sm-', $class);
    });


    // Filter classes
    $classes = array_intersect($classes, $this->fields);

    return $classes;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ADD CLASSES //////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Add classes to a field
   *
   * @param Field $field
   * @param array $classes The possible classes to add
   *
   * @return Field
   */
  public function getFieldClasses(Field $field, $classes)
  {
    // Add inline class for checkables
    if ($field->isCheckable() and in_array('inline', $classes)) {
      $field->inline();
    }

    // Filter classes according to field type
    if ($field->isButton()) $classes = $this->filterButtonClasses($classes);
    else $classes = $this->filterFieldClasses($classes);

    if(!$field->isCheckable() && !$field->isButton() && !in_array($field->getType(), ['checkbox', 'radio']))
    {
      array_unshift($classes, 'form-control');
    }

    // If we found any class, add them
    if ($classes) {
      $field->class(implode(' ', $classes));
    }

    return $field;
  }

  /**
   * Add group classes
   *
   * @return string A list of group classes
   */
  public function getGroupClasses()
  {
    return 'form-group';
  }

  /**
   * Add label classes
   *
   * @param  array $attributes An array of attributes
   * @return array An array of attributes with the label class
   */
  public function getLabelClasses()
  {
    $type = $this->app['former']->form()->getType();

    if($type == 'horizontal')
    {
      return 'control-label col-sm-4';
    }
    else
    {
      return 'control-label';
    }
  }

  /**
   * Add uneditable field classes
   *
   * @param  array $attributes The attributes
   * @return array An array of attributes with the uneditable class
   */
  public function getUneditableClasses()
  {
    return 'uneditable-input';
  }

  /**
   * Add form class
   *
   * @param  array  $attributes The attributes
   * @param  string $type       The type of form to add
   * @return array
   */
  public function getFormClasses($type)
  {
    return $type ? 'form-'.$type : null;
  }

  /**
   * Add actions block class
   *
   * @param  array  $attributes The attributes
   * @return array
   */
  public function getActionClasses()
  {
    $type = $this->app['former']->form()->getType();

    if($type == 'horizontal')
    {
      return 'col-sm-8 col-sm-offset-4';
    }
    else
    {
      return '';
    }


  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// RENDER BLOCKS /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Render an help text
   *
   * @param string $text
   * @param array  $attributes
   *
   * @return string
   */
  public function createHelp($text, $attributes = array())
  {
    return Element::create('span', (string) __($text), $attributes)->addClass('help-block');
  }

  /**
   * Render a block help text
   *
   * @param string $text
   * @param array  $attributes
   *
   * @return string
   */
  public function createBlockHelp($text, $attributes = array())
  {
    return Element::create('p', (string) __($text), $attributes)->addClass('help-block');
  }

  /**
   * Render a disabled field
   *
   * @param Field $field
   *
   * @return string
   */
  public function createDisabledField(Field $field)
  {
    return Element::create('span', $field->getValue(), $field->getAttributes());
  }

  /**
   * Render an icon
   *
   * @param string $icon       The icon name
   * @param array  $attributes Its attributes
   *
   * @return string
   */
  public function createIcon($iconType, $attributes = array())
  {
    $icon = Element::create('i', null, $attributes);

    // White icon
    if (String::contains($iconType, 'white')) {
      $iconType = String::remove($iconType, 'white');
      $iconType = trim($iconType, '-');
      $icon->addClass('glyphicon-white');
    }

    // Check for empty icons
    if (!$iconType) return false;

    // Create icon
    $icon->addClass('glyphicon-'.$iconType);

    return $icon;
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// WRAP BLOCKS ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Wrap a field with potential additional tags
   *
   * @param  Field $field
   * @return string A wrapped field
   */
  public function wrapField($field)
  {
    $elem = Element::create('div', $field);

    $formType = $this->app['former']->form()->getType();

    if($formType === 'horizontal')
    {
      $elem->addClass('col-sm-8');
    }

    return $elem;
  }

}
