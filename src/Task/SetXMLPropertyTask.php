<?php

/**
 * @file
 * Contains SetXMLPropertyTask.
 */

require_once 'phing/Task.php';

/**
 * A Phing task to set the value of an XML element attribute.
 */
class SetXMLPropertyTask extends Task {

  /**
   * The XML file path.
   *
   * @var string
   */
  protected $file;

  /**
   * The XPath query for the element to change.
   *
   * @var string
   */
  protected $element;

  /**
   * The attribute to change.
   *
   * @var string
   */
  protected $attribute;

  /**
   * The value to set.
   *
   * @var string
   */
  protected $value;

  /**
   * Sets the XML file path.
   *
   * @param string $file
   *   The XML file path.
   */
  public function setFile($file) {
    $this->file = $file;
  }

  /**
   * Sets the XPath element query.
   *
   * @param string $element
   *   The XPath element query.
   */
  public function setElement($element) {
    $this->element = $element;
  }

  /**
   * Sets the attribute to change.
   *
   * @param string $attribute
   *   The attribute to change.
   */
  public function setAttribute($attribute) {
    $this->attribute = $attribute;
  }

  /**
   * Sets the value to set.
   *
   * @param string $value
   *   The value to set.
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function main() {
    $doc = new DOMDocument();
    $doc->load($this->file);

    (new DOMXPath($doc))
      ->query($this->element)
      ->item(0)
      ->setAttribute($this->attribute, $this->value);

    $doc->save($this->file);
  }

}
