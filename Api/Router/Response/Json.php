<?php

namespace Api\Router\Response;

class Json extends AbstractFormat {

	public function getContent() {
		$response = $this->response;
		$data = [
			'date' => self::getFormattedDate(),
			'status' => [
				'code' => self::getStatusCode($response),
				'message' => self::getStatusMessage($response)
			],
			'result' => null
		];
		if (!$response->isValid()) {
			$data['status']['error'] = $response->getErrorCode();
			//if (($exception = $response->getException()))
			//	$data['status']['trace'] = $exception->getTraceAsString();
		}
		$result = $response->get();
		$results = $response->getResults();
		if ($results) {
			$data['result'] = [];
			if (null !== $result)
				$data['result'][] = $result;
			foreach ($results as $result) {
				$data['result'][] = $result->get();
			}
		} else {
			$data['result'] = $result;
		}
		return json_encode($data);
	}

}