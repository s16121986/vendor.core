<?php
namespace Form\Element;

class Checkbox extends Xhtml{

    protected $_checked = false;

    protected $_options = array(
        'checkedValue'   => 1,
        'uncheckedValue' => 0,
    );

    public function setOptions($options) {
        parent::setOptions($options);

        $curValue = $this->getValue();
        $test     = array($this->checkedValue, $this->uncheckedValue);
        if (!in_array($curValue, $test)) {
            $this->setValue($curValue);
        }

        return $this;
    }

	public function isValid() {
		return (!$this->required || null !== $this->_value);
	}

	public function getValue() {
		$value = parent::getValue();
		return $value == $this->checkedValue ? $this->checkedValue : $this->uncheckedValue;
	}

    public function setValue($value) {
        if ($value == $this->checkedValue) {
            parent::setValue($this->checkedValue);
            $this->_checked = true;
        } else {
            parent::setValue($this->uncheckedValue);
            $this->_checked = false;
        }
        return $this;
    }

    public function setChecked($flag) {
        $this->_checked = (bool) $flag;
        if ($this->_checked) {
            $this->setValue($this->checkedValue);
        } else {
            $this->setValue($this->uncheckedValue);
        }
        return $this;
    }

    public function isChecked() {
        return ($this->getValue() == $this->checkedValue);
    }

	public function getHtml() {
		return '<input type="checkbox"' . $this->attrToString() . ' value="' . $this->checkedValue . '"' . ($this->isChecked() ? ' checked="checked"' : '') . ' />';
	}

}
