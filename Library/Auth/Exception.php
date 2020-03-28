<?php
namespace Auth;

use Exception as AbstractException;

class Exception extends AbstractException{
	
	const INCORRECT_DATA				 = 1;
	const USER_NOT_FOUND = 2;
	
	const FAILURE                        =  0;
    const FAILURE_IDENTITY_NOT_FOUND     = -1;
    const FAILURE_IDENTITY_AMBIGUOUS     = -2;
    const FAILURE_CREDENTIAL_INVALID     = -3;
    const FAILURE_UNCATEGORIZED          = -4;
	const AUTHORIZATION_EXPIRED			 = -5;
	const NOT_AUTHORIZED				 = -6;
	const INCORRECT_IDENTITY			 = -8;
	const REGISTRATION_INCOMPLETE		 = -9;
	const CLIENT_NOT_FOUND				 = -10;
	const NOT_LICENSE					 = -11;
	const APPSTORE_ERROR				 = -12;
	const SUCCESS                        =  1;
	
}