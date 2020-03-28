<?php
namespace Form\Action;

class ApiSubmit extends Submit{

	protected $_options = array(
		'write' => true
	);

	public function submit() {
		$return = parent::submit();
		if ($return) {
			$form = $this->_form;
			if ($form->api) {
				try {
					$form->api->setData($form->getData());
					
					if ($this->write) {
						return $form->api->write();
					}
				} catch (\Api\Attribute\Exception $e) {
					$error = lang($e);
					if (empty($error)) {
						$error = true;
					}
					if ($form->getElement($e->attribute)) {
						$form->getElement($e->attribute)->setError($error);
					} else {
						$form->addError($error);
					}
					$return = false;
				}
			}
		}
		return $return;
	}

}