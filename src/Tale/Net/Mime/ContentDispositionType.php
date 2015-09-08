<?php

namespace Tale\Net\Mime;

use Tale\System\Enum;

//TODO: ContentDispositionType or MimeContentDispositionType?! Second one would be consistent, but a pain to write
class ContentDispositionType extends Enum
{

	const INLINE = 'inline';
	const ATTACHMENT = 'attachment';
	const FORM_DATA = 'form-data';
	const SIGNAL = 'signal';
	const ALERT = 'alert';
	const ICON = 'icon';
	const RENDER = 'render';
	const RECIPIENT_LIST_HISTORY = 'recipient-list-history';
	const SESSION = 'session';
	const AIB = 'aib';
	const EARLY_SESSION = 'early-session';
	const RECIPIENT_LIST = 'recipient-list';
	const NOTIFICATION = 'notification';
	const BY_REFERENCE = 'by-reference';
	const INFO_PACKAGE = 'info-package';
}