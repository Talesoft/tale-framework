<?php

namespace Tale\Dom;

class Element extends Node
{

    const ATTRIBUTE_ID = 'id';
    const ATTRIBUTE_CLASS = 'class';

    private $_tag;
    private $_attributes;

    /**
     * @param string     $tag
     * @param array|null $attributes
     * @param Node|null  $parent
     * @param array|null $children
     */
    public function __construct($tag, array $attributes = null, Node $parent = null, array $children = null)
    {

        parent::__construct($parent, $children);

        $this->_tag = $tag;
        $this->_attributes = $attributes ? $attributes : [];
    }

    public function getTag()
    {

        return $this->_tag;
    }

    public function setTag($name)
    {

        $this->_tag = $name;

        return $this;
    }

    public function hasAttributes()
    {

        return count($this->_attributes) > 0;
    }

    public function getAttributes()
    {

        return $this->_attributes;
    }

    public function setAttributes(array $attributes)
    {

        $this->_attributes = $attributes;

        return $this;
    }

    public function hasAttribute($name)
    {

        return isset($this->_attributes[$name]);
    }

    public function getAttribute($name)
    {

        return $this->_attributes[$name];
    }

    public function setAttribute($name, $value)
    {

        $this->_attributes[$name] = $value;

        return $this;
    }

    public function removeAttribute($name)
    {

        unset($this->_attributes[$name]);

        return $this;
    }

    public function hasId()
    {

        return $this->hasAttribute(self::ATTRIBUTE_ID);
    }

    public function getId()
    {

        return $this->getAttribute(self::ATTRIBUTE_ID);
    }

    public function setId($id)
    {

        return $this->setAttribute(self::ATTRIBUTE_ID, $id);
    }

    public function hasClasses()
    {

        return $this->hasAttribute(self::ATTRIBUTE_CLASS);
    }

    public function getClasses()
    {

        return $this->getAttribute(self::ATTRIBUTE_CLASS);
    }

    public function setClasses($classes)
    {

        return $this->setAttribute(self::ATTRIBUTE_CLASS, $classes);
    }

    public function getClassArray()
    {

        if (!$this->hasClasses())
            return [];

        return explode(' ', $this->getClasses());
    }

    public function setClassArray(array $classes)
    {

        return $this->setClasses(implode(' ', $classes));
    }

    public function hasClass($class)
    {

        if (!$this->hasClasses())
            return false;

        return in_array($class, $this->getClassArray());
    }

    public function appendClass($class)
    {

        $classes = $this->getClassArray();
        $classes[] = $class;

        return $this->setClassArray($classes);
    }

    public function prependClass($class)
    {

        $classes = $this->getClassArray();
        array_unshift($classes, $class);

        return $this->setClassArray($classes);
    }

    public function removeClass($class)
    {

        $classes = $this->getClassArray();
        $idx = array_search($class, $classes);

        if ($idx === false)
            return $this;

        unset($classes[$idx]);

        return $this->setClassArray($classes);
    }

    public function getText()
    {

        return trim(implode('', $this->getTextArray()));
    }

    public function setText($text)
    {

        return $this->removeChildren()->appendChild(new Text($text));
    }

    public function matches($selector)
    {

        $selector = $selector instanceof Selector ? $selector : Selector::fromString($selector);

        return $selector->matches($this);
    }

    public function findElements($selector, $recursive = false)
    {

        $selector = $selector instanceof Selector ? $selector : Selector::fromString($selector);

        foreach ($this->getElements() as $child) {

            if ($child instanceof Element) {

                if ($child->matches($selector))
                    yield $child;

                if ($recursive)
                    foreach ($child->findElements($selector, $recursive) as $subChild)
                        yield $subChild;
            }
        }
    }

    public function findElementArray($selector, $recursive = false)
    {

        return iterator_to_array($this->findElements($selector, $recursive));
    }

    public function find($selectors)
    {

        //We add a , to the selector to trigger the "," selector below and flush the results
        $selectors = preg_split('/(,| |>)/', "$selectors,", -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);

        $recursive = true;
        $currentSet = [$this];
        foreach ($selectors as $selector) {

            $selector = trim($selector);

            if (empty($selector))
                continue;

            if ($selector === '>') {

                $recursive = false;
                continue;
            }

            if ($selector === ',') {

                foreach ($currentSet as $child)
                    yield $child;

                $recursive = true;
                $currentSet = [$this];
                continue;
            }

            foreach ($currentSet as $child)
                $currentSet = $child->findElementArray($selector, $recursive);

            $recursive = true;
        }
    }

    public function findArray($selectors)
    {

        return iterator_to_array($this->find($selectors));
    }

    /**
     * @param array|null $options
     *
     * @return string
     */
    public function getString(array $options = null)
    {

        $writerClassName = static::getWriterClassName();
        $writer = new $writerClassName($options);

        return $writer->writeLeaf($this);
    }

    public function __toString()
    {

        return $this->getString();
    }

    //TODO: getArray()

    public static function fromSelector($selector, Node $parent = null, array $children = null)
    {

        $selector = $selector instanceof Selector ? $selector : Selector::fromString($selector);

        $tag = $selector->getTag();
        $el = new static($tag ? $tag : 'div', $selector->getAttributes(), $parent, $children);

        if ($id = $selector->getId())
            $el->setId($id);

        if ($classes = $selector->getClasses())
            foreach ($classes as $class)
                $el->appendClass($class);

        return $el;
    }

    public static function fromString($string, array $options = null)
    {

        $parserClassName = static::getParserClassName();
        $parser = new $parserClassName($options);

        return $parser->parse($string);
    }

    public static function fromFile($path, array $options = null)
    {

        return static::fromString(file_get_contents($path), $options);
    }

    public static function getWriterClassName()
    {

        return __NAMESPACE__.'\\Writer';
    }

    public static function getParserClassName()
    {

        return __NAMESPACE__.'\\Parser';
    }
}