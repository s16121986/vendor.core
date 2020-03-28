<?php

abstract class PAGE_OPTIONS extends Enum{
	const XML_PRIORITY = 'xml_priority';
}
abstract class PAGE_HEAD_META_NAME extends Enum{
	const TITLE = 'title';
	const KEYWORDS = 'keywords';
	const DESCRIPTION = 'description';
	const TITLE_PREFIX = 'title_prefix';
	const LANGUAGE = 'language';
	const ROBOTS = 'robots';
	const COPYRIGHT = 'copyright';
	const CONTENT_LANGUAGE = 'viewport';
	const APPLICATION_NAME = 'application-name';
}
abstract class PAGE_HEAD_LINK_REL extends Enum{
	const ICON = 'icon';
	const SHORTCUT_ICON = 'shortcut icon';
	const PUBLISHER = 'publisher';
	const DNS_PREFETCH = 'dns-prefetch';
	const VIDEO_SRC = 'video_src';
	const APPLE_TOUCH_ICON = 'apple-touch-icon';
	const CANONICAL = 'canonical';
	//<link href="https://news.yandex.ru/index.rss" title="RSS" type="application/rss+xml" rel="alternate"/>
	const ALTERNATE = 'alternate';
	const NEXT = 'next';
	const PREV = 'prev';
}
abstract class PAGE_HEAD_META_HTTP_ENQUIV extends Enum{
	const X_UA_COMPATIBLE = 'X-UA-Compatible';
	const CONTENT_TYPE = 'Content-Type';
	const CONTENT_LANGUAGE = 'Content-language';
	const X_DNS_PREFETCH_CONTROL = 'x-dns-prefetch-control';
}
abstract class PAGE_HEAD_META_NAME_OG extends Enum{
	// Обязательные поля
	const TITLE = 'og:title';
	const TYPE = 'og:type';
	const URL = 'og:url';
	const SITE_NAME = 'og:site_name';
	const DESCRIPTION = 'og:description';
	
	// Контактная информация
	const EMAIL = 'og:email';
	const PHONE_NUMBER = 'og:phone_number';
	const FAX_NUMBER = 'og:fax_number';
	// Месторасположение
	const LOCALE = 'og:locale';
	const LOCALE_ALTERNATE = 'og:locale:alternate';
	const LATITUDE = 'og:latitude';
	const LONGITUDE = 'og:longitude';
	const STREET_ADDRESS = 'og:street-address';
	const LOCALITY = 'og:locality';
	const REGION = 'og:region';
	const POSTAL_CODE = 'og:postal-code';
	const COUNTRY_NAME = 'og:country-name';
	//image
	const IMAGE = 'og:image';
	const IMAGE_SECURE_URL = 'og:image:secure_url';
	const IMAGE_TYPE = 'og:image:type';
	const IMAGE_WIDTH = 'og:image:width';
	const IMAGE_HEIGHT = 'og:image:height';
	// Видео
	const VIDEO = 'og:video';
	const VIDEO_SECURE_URL = 'og:video:secure_url';
	const VIDEO_TYPE = 'og:video:type';
	const VIDEO_HEIGHT = 'og:video:height';
	const VIDEO_WIDTH = 'og:video:width';
	// Аудио
	const AUDIO = 'og:audio';
	const AUDIO_TYPE = 'og:audio:type';
	const AUDIO_SECURE_URL = 'og:audio:secure_url';
	const AUDIO_TITLE = 'og:audio:title';
	const AUDIO_ARTIST = 'og:audio:artist';
	const AUDIO_ALBUM = 'og:audio:album';
}
abstract class PAGE_HEAD_META_NAME_FACEBOOK extends Enum{
	const APP_ID = 'fb:app_id';
}
abstract class PAGE_HEAD_META_NAME_TWITTER extends Enum{
	const ACCOUNT_ID = 'twitter:account_id';
	const TITLE = 'twitter:title';
	const DESCRIPTION = 'twitter:description';
	const IMAGE = 'twitter:image';
}
abstract class PAGE_HEAD_META_NAME_MSAPPLICATION extends Enum{
	const STARTURL = 'msapplication-starturl';
	const TITLE_COLOR = 'msapplication-TileColor';
	const TITLE_TOOLTIP = 'msapplication-tooltip';
	const TITLE_IMAGE = 'msapplication-TileImage';
	const TASK = 'msapplication-task';
}