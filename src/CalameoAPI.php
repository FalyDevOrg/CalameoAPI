<?php
	declare(strict_types=1);

	namespace Fawno\Calameo;

	use CURLFile;
	use DOMDocument;
	use SimpleXMLElement;

	class CalameoAPI {
		protected const API = 'https://api.calameo.com/1.0/';
		protected const UPLOAD = 'https://upload.calameo.com/1.0/';

		protected $config;
		protected $curl;

		// Initialize API with config.
		// Config can be an object json, xml, an associative array, xml string, json string, a file xml or a file json
		public function __construct ($config = null) {
			if (!empty($config)) $this->set_config($config);
			$this->curl = curl_init();
		}

		public function set_config ($config) {
			switch (gettype($config)) {
				case 'string':
					if (is_file($config)) $config = file_get_contents($config);
					if ($config[0] == '<') {
						$config = new SimpleXMLElement($config);
					} else break;
				case 'array':
				case 'object':
					$config = json_encode($config);
					break;
				default:
					return false;
			}
			$this->config = json_decode($config);
			return true;
		}

		public function __destruct () {
			curl_close($this->curl);
		}

		// Sign Request
		// https://developer.calameo.com/content/api/#sign
		protected function signRequest($fields) {
			if (isset($fields['file'])) {
				unset($fields['file']);
			}

			ksort($fields);
			$signature = $this->config->secret;
			foreach ($fields as $name => $value) {
				$signature .= $name . $value;
			}

			return md5($signature);
		}

		// Do request with common params:
		//		Name					Required	Type 				Description
		//		apikey				yes 			string 			API public key
		//		signature			yes 			string 			Signature of the request.
		//		expires				yes 			timestamp 	UNIX timestamp for request expiration (GMT).
		//		output 									string 			Format of the response. Either XML (default), JSON or PHP.
		protected function doRequest ($fields) {
			$fields['expires'] = strtotime('+120min'); // UNIX timestamp for request expiration (GMT).
			$fields['output'] = 'JSON';
			$fields['apikey'] = $this->config->apikey;
			$fields['signature'] = $this->signRequest($fields);

			curl_setopt($this->curl, CURLOPT_URL, empty($fields['file']) ? self::API : self::UPLOAD);
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($this->curl);

			if (200 == curl_getinfo($this->curl, CURLINFO_HTTP_CODE)) {
				// API Fail on output format
				if (substr($response, 0, 5) == '<?xml') {
					$response = json_encode(new SimpleXMLElement($response));
				}
			} else {
				$response = false;
			}

			return $response;
		}

		// API.getAccountInfos
		//		https://developer.calameo.com/content/api/#getAccountInfos
		//		This action allows you to recover the information about your account.
		// Response:
		//		Name					Type			Description
		//		ID						integer		Account's ID
		//		Name					string		Account's name.
		//		City					string		Town/city of the account.
		//		Country				string		Country of the account, using the official two-letter code format
		//		WebsiteName		string		Name of the website.
		//		WebsiteUrl		string		Address of the website.
		//		PublicUrl			string		Public URL of the account.
		public function getAccountInfos () {
			$fields['action'] = __FUNCTION__;

			return json_decode($this->doRequest($fields));
		}

		// API.fetchAccountSubscriptions
		//		https://developer.calameo.com/content/api/#fetchAccountSubscriptions
		//		This action allows you to recover all or part of the subscriptions of your account.
		// Request:
		//		Name							Required	Type 				Description
		//		order												string			String of characters used to define the organization criteria of the subscriptions
		//		way													string			String of characters used to define the sort order. Either UP (default) or DOWN.
		//		start												integer			Start position of the range of subscriptions. Default is 0.
		//		step												integer			Number of subscriptions to be sent from the start position (max: 50).
		// Response:
		//		This request sends an array of Folders.
		//		https://developer.calameo.com/content/api/#responseSubscription
		//			Name					Type			Description
		//			ID						integer		Unique identifying key for the subscription.
		//			AccountID			integer		Unique identifying key for the subscription's account.
		//			Name					string		Title of the subscription.
		//			Description		string		Description of the subscription.
		//			Books					integer		Available publications inside the subscription
		//			Subscribers		integer		Available subscribers inside the subscription (only returned for your account's subscription).
		//			Creation			datetime	Date of creation of the subscription
		//			Modification	datetime	Date of the last modification of the subscription.
		//			PublicUrl			string		Absolute URL for the subscription's overview.
		public function fetchAccountSubscriptions (array $fields = []) {
			$fields['action'] = __FUNCTION__;

			return json_decode($this->doRequest($fields));
		}

		public function fetchAllAccountSubscriptions() {
			$fields = [
				'start' => 0,
				'step' => 50,
			];

			$subscriptions = [];
			do {
				$result = $this->fetchAccountSubscriptions($fields);
				if ($result->response->status == 'ok') {
					$subscriptions = array_merge($subscriptions, $result->response->content->items);
					$fields['start'] += $fields['step'];
				}
			} while ($fields['start'] < $result->response->content->total);

			return $subscriptions;
		}

		// API.fetchAccountBooks
		//		https://developer.calameo.com/content/api/#fetchAccountBooks
		//		This action allows you to fetch your account's publications.
		// Request:
		//		Name							Required	Type 				Description
		//		order												string			String of characters used to define the organization criteria of the subscriptions
		//		way													string			String of characters used to define the sort order. Either UP (default) or DOWN.
		//		start												integer			Start position of the range of subscriptions. Default is 0.
		//		step												integer			Number of subscriptions to be sent from the start position (max: 50).
		// Response:
		//		This method returns an array of Publications.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function fetchAccountBooks ($fields = array()) {
			$fields['action'] = __FUNCTION__;

			return json_decode($this->doRequest($fields));
		}

		public function fetchAllAccountBooks() {
			$fields['start'] = 0;
			$fields['step'] = 50;
			$books = array();
			do {
				$result = $this->fetchAccountBooks($fields);
				if ($result->response->status == 'ok') {
					foreach ($result->response->content->items as $book) $books[$book->ID] = $book;
					$fields['start'] += $fields['step'];
				}
			} while ($fields['start'] < $result->response->content->total);

			return $books;
		}

		// API.fetchAccountSubscribers
		//		https://developer.calameo.com/content/api/#fetchAccountSubscribers
		//		This action allows you to fetch your account's subscribers.
		// Request:
		//		Name							Required	Type 				Description
		//		order												string			String of characters used to define the organization criteria of the subscriptions
		//		way													string			String of characters used to define the sort order. Either UP (default) or DOWN.
		//		start												integer			Start position of the range of subscriptions. Default is 0.
		//		step												integer			Number of subscriptions to be sent from the start position (max: 50).
		// Response:
		//		This method returns an array of Subscribers.
		//		https://developer.calameo.com/content/api/#responseSubscriber
		//			Name						Type			Description
		//			AccountID				integer		Subscriber's owner account ID (should be your account ID).
		//			SubscriptionID	integer		Subscriber's subscription ID.
		//			LastName				string		Last name of the subscriber.
		//			FirstName				string		First name of the subscriber.
		//			Email						string		Email address of the subscriber.
		//			Login						string		Login of the subscriber.
		//			Password				string		Password of the subscriber.
		//			IsActive				boolean		Activation status of the subscriber. Either 1 (activated), 0 (deactivated).
		//			LastLogin				datetime	Date of the subscriber's last login.
		//			Creation				datetime	Date the subscriber was created.
		//			Modification		datetime	Date the subscriber was last edited.
		//			Extras					string		Additional information on the subscriber, in varchar format up to 255 characters in size
		public function fetchAccountSubscribers (array $fields = []) {
			$fields['action'] = __FUNCTION__;

			return json_decode($this->doRequest($fields));
		}

		// API.getSubscriptionInfos
		//		https://developer.calameo.com/content/api/#getSubscriptionInfos
		//		This action allows you to recover the information about a subscription.
		// Request:
		//		Name							Required	Type 				Description
		//		subscription_id		yes				integer 		ID of the subscirption.
		// Response:
		//		Returns a Folder.
		//		https://developer.calameo.com/content/api/#responseSubscription
		//			Name						Type			Description
		//			ID							integer		Unique identifying key for the subscription.
		//			AccountID				integer		Unique identifying key for the subscription's account.
		//			Name						string		Title of the subscription.
		//			Description			string		Description of the subscription.
		//			Books						integer		Available publications inside the subscription
		//			Subscribers			integer		Available subscribers inside the subscription (only returned for your account's subscription).
		//			Creation				datetime	Date of creation of the subscription
		//			Modification		datetime	Date of the last modification of the subscription.
		//			PublicUrl				string		Absolute URL for the subscription's overview.
		public function getSubscriptionInfos (int $subscription_id) {
			$fields['action'] = __FUNCTION__;
			$fields['subscription_id'] = $subscription_id;

			return json_decode($this->doRequest($fields));
		}

		// API.fetchSubscriptionBooks
		//		https://developer.calameo.com/content/api/#fetchSubscriptionBooks
		//		This action allows you to fetch a subscription's publications.
		// Request:
		//		Name							Required	Type 				Description
		//		subscription_id		yes				integer 		ID of the subscirption.
		//		order												string			String of characters used to define the organization criteria of the subscriptions
		//		way													string			String of characters used to define the sort order. Either UP (default) or DOWN.
		//		start												integer			Start position of the range of subscriptions. Default is 0.
		//		step												integer			Number of subscriptions to be sent from the start position (max: 50).
		// Response:
		//		This method returns an array of Publications.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function fetchSubscriptionBooks (int $subscription_id, array $fields = []) {
			$fields['action'] = __FUNCTION__;
			$fields['subscription_id'] = $subscription_id; // ID of the subscription.

			return json_decode($this->doRequest($fields));
		}

		// API.fetchSubscriptionSubscribers
		//		https://developer.calameo.com/content/api/#fetchSubscriptionSubscribers
		//		This action allows you to fetch a subscription's subscribers.
		// Request:
		//		Name							Required	Type 				Description
		//		subscription_id		yes				integer 		ID of the subscirption.
		//		order												string			String of characters used to define the organization criteria of the subscriptions
		//		way													string			String of characters used to define the sort order. Either UP (default) or DOWN.
		//		start												integer			Start position of the range of subscriptions. Default is 0.
		//		step												integer			Number of subscriptions to be sent from the start position (max: 50).
		// Response:
		//		This method returns an array of Subscribers.
		//		https://developer.calameo.com/content/api/#responseSubscriber
		//			Name						Type			Description
		//			AccountID				integer		Subscriber's owner account ID (should be your account ID).
		//			SubscriptionID	integer		Subscriber's subscription ID.
		//			LastName				string		Last name of the subscriber.
		//			FirstName				string		First name of the subscriber.
		//			Email						string		Email address of the subscriber.
		//			Login						string		Login of the subscriber.
		//			Password				string		Password of the subscriber.
		//			IsActive				boolean		Activation status of the subscriber. Either 1 (activated), 0 (deactivated).
		//			LastLogin				datetime	Date of the subscriber's last login.
		//			Creation				datetime	Date the subscriber was created.
		//			Modification		datetime	Date the subscriber was last edited.
		//			Extras					string		Additional information on the subscriber, in varchar format up to 255 characters in size
		public function fetchSubscriptionSubscribers (int $subscription_id, array $fields = []) {
			$fields['action'] = __FUNCTION__;
			$fields['subscription_id'] = $subscription_id; // ID of the subscription.

			return json_decode($this->doRequest($fields));
		}

		// API.getBookInfos
		//		https://developer.calameo.com/content/api/#getBookInfos
		//		This action allows you to recover the information about a publication using its unique code.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		// Response:
		//		Returns a Publication.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function getBookInfos (string $book_id) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.activateBook
		//		https://developer.calameo.com/content/api/#activateBook
		//		This action allows you to activate a publication.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		// Response:
		//		This request sends the character string ok if successful.
		public function activateBook (string $book_id) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.deactivateBook
		//		https://developer.calameo.com/content/api/#deactivateBook
		//		This action allows you to deactivate a publication.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		// Response:
		//		This request sends the character string ok if successful.
		public function deactivateBook (string $book_id) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.updateBook
		//		https://developer.calameo.com/content/api/#updateBook
		//		This action allows you to update a publication's properties.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		//		category										string			Category. http://help.calameo.com/index.php?title=API:Category_(references)
		//		format											string			Format. http://help.calameo.com/index.php?title=API:Format_(references)
		//		dialect											string			Dialect. http://help.calameo.com/index.php?title=API:Language_(references)
		//		name												string			Title of the publication. If not present, the filename will be used.
		//		description									string			Description of the publication. If not present, the first page's text will be used.
		//		date												date				Date of the publication for DRM management.
		//		is_published								boolean			Activation status. Either 0 (disabled) or 1 (enabled).
		//		publishing_mode							integer			Access to the publication. Either 1 (public) or 2 (private).
		//		private_url									boolean			Use a private URL. Either 0 (disabled) or 1 (enabled).
		//		view												string			Default view ing mode. Either book, slide, scroll.
		//		subscribe										integer			Allow subscribers' access. Either 0 (disabled) or 1 (enabled).
		//		comment											integer			Comments behaviour. Either 0 (disabled), 1 (moderate all), 2 (moderate all except contacts), 3 (accept only contacts) or 4 (accept all).
		//		download										integer			Download behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		print												integer			Print behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		share												integer			NEW	Share menu. Either 0 (disabled), 1 (enabled). Enabled by default.
		//		annotation_view							integer			DEPRECATED	Annotation viewing behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		annotation_add							integer			DEPRECATED	Annotation adding behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		mini												integer			Allow MiniCalamÃ©o. Either 0 (disabled) or 1 (enabled).
		//		adult												integer			Restrict access to adults. Either 0 (no) or 1 (yes).
		//		direction										integer			Reading direction. Either 0 (left-to-right) or 1 (right-to-left "manga mode").
		//		license											string			License. Either <empty> (traditionnal copyright) or pd (public domain), by, by_nc, by_nc_nd, by_nc_sa, by_nd or by_sa (Creative Commons).
		//		skin_url										string			Custom skin URL Must be an absolute URL.
		//		logo_url										string			Custom logo URL. Must be an absolute URL.
		//		logo_link_url								string			Custom logo link URL. Must be an absolute URL.
		//		background_url							string			Custom background URL. Must be an absolute URL.
		//		music												integer			Background music mode. Either 0 (loop forever), 1 (play only once).
		//		music_url										string			Custom background music URL. Must be an absolute URL.
		//		sfx													integer			Play sound effects like page flipping. Either 0 (disabled) or 1 (enabled).
		//		sfx_url											string			Custom page flipping sound URL. Must be an absolute URL.
		//
		//		Note: If any property is missing from the request, its value will not be updated.
		// Response:
		//		Returns a Publication.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function updateBook (string $book_id, array $fields = []) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.deleteBook
		//		https://developer.calameo.com/content/api/#deleteBook
		//		This action allows you to delete a publication of your subscription using its unique code.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		// Response:
		//		This request sends the character string ok if successful.
		public function deleteBook (string $book_id) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.fetchBookTocs
		//		https://developer.calameo.com/content/api/#fetchBookTocs
		//		This action allows you to get the table of content of a publication.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		// Response:
		//		Returns an array of TOC items:
		//			Name					Type			Description
		//			Level					integer		Hierarchy level of the item. From 1 to the hightest.
		//			Name					string		Label of the item
		//			PageNumber		integer		Page number linked to the item.
		public function fetchBookTocs (string $book_id) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.fetchBookComments
		//		https://developer.calameo.com/content/api/#fetchBookComments
		//		This action allows you to get the comments of a publication.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		//		order												string			String of characters used to define the organization criteria of the subscriptions
		//		way													string			String of characters used to define the sort order. Either UP (default) or DOWN.
		//		start												integer			Start position of the range of subscriptions. Default is 0.
		//		step												integer			Number of subscriptions to be sent from the start position (max: 50).
		// Response:
		//		Returns an array of comments:
		//			Name						Type			Description
		//			PosterID				integer		ID of the comment poster.
		//			PosterName			string		Name of the comment poster.
		//			PosterPublicUrl	string		Absolute URL for the comment poster's page.
		//			PosterThumbUrl	string		Absolute URL for the comment poster's thumbnail.
		//			Date						date			Date of the comment.
		//			Text						string		Text of the comment.
		public function fetchBookComments (string $book_id, array $fields = []) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.renewBookPrivateUrl
		//		https://developer.calameo.com/content/api/#renewBookPrivateUrl
		//		This action allows you to renew a publication's private URL using its unique code.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		// Response:
		//		Return a Publication with the new private URL.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function renewBookPrivateUrl (string $book_id) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;

			return json_decode($this->doRequest($fields));
		}

		// API.publish
		//		https://developer.calameo.com/content/api/#publish
		//		This action allows you to publish a document.
		// Request:
		//		Name							Required	Type 				Description
		//		file							yes				file				Document to be uploaded (like provided by a HTML form file field).
		//		subscription_id		yes				integer			ID of the subscription.
		//		category					yes				string			Category. http://help.calameo.com/index.php?title=API:Category_(references)
		//		format						yes				string			Format. http://help.calameo.com/index.php?title=API:Format_(references)
		//		dialect						yes				string			Dialect. http://help.calameo.com/index.php?title=API:Language_(references)
		//		name												string 			Title of the publication. If not present, the filename will be used.
		//		description									string 			Description of the publication. If not present, the first page's text will be used.
		//		date												date 				Date of the publication for DRM management (YYYY-MM-DD).
		//		is_published								boolean 		Activation status. Either 0 (disabled) or 1 (enabled).
		//		publishing_mode							integer 		Access to the publication. Either 1 (public) or 2 (private).
		//		private_url									boolean 		Use a private URL. Either 0 (disabled) or 1 (enabled).
		//		view												string 			Default view ing mode. Either book, slide, scroll.
		//		subscribe										integer 		Allow subscribers' access. Either 0 (disabled) or 1 (enabled).
		//		comment											integer 		Comments behaviour. Either 0 (disabled), 1 (moderate all), 2 (moderate all except contacts), 3 (accept only contacts) or 4 (accept all).
		//		download										integer 		Download behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		print												integer 		Print behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		share												integer 		NEW	Share menu. Either 0 (disabled), 1 (enabled). Enabled by default.
		//		annotation_view							integer 		DEPRECATED	Annotation viewing behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		annotation_add							integer 		DEPRECATED	Annotation adding behaviour. Either 0 (disabled), 1 (only contacts) or 2 (everyone).
		//		mini												integer 		Allow MiniCalamÃ©o. Either 0 (disabled) or 1 (enabled).
		//		adult												integer 		Restrict access to adults. Either 0 (no) or 1 (yes).
		//		direction										integer 		Reading direction. Either 0 (left-to-right) or 1 (right-to-left "manga mode").
		//		license											string 			License. Either <empty> (traditionnal copyright) or pd (public domain), by, by_nc, by_nc_nd, by_nc_sa, by_nd or by_sa (Creative Commons).
		//		skin_url										string 			Custom skin URL Must be an absolute URL.
		//		logo_url										string 			Custom logo URL. Must be an absolute URL.
		//		logo_link_url								string 			Custom logo link URL. Must be an absolute URL.
		//		background_url							string 			Custom background URL. Must be an absolute URL.
		//		music												integer 		Background music mode. Either 0 (loop forever), 1 (play only once).
		//		music_url										string 			Custom background music URL. Must be an absolute URL.
		//		sfx													integer 		Play sound effects like page flipping. Either 0 (disabled) or 1 (enabled).
		//		sfx_url											string 			Custom page flipping sound URL. Must be an absolute URL.
		// Response:
		//		Returns a Publication.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function publish (string $file, array $fields = []) {
			$fields['action'] = __FUNCTION__;
			$fields['file'] = new CURLFile($file, mime_content_type($file), basename($file));

			return json_decode($this->doRequest($fields));
		}

		// API.revise
		//		https://developer.calameo.com/content/api/#revise
		//		This action allows you to publish a new revision of a document.
		// Request:
		//		Name							Required	Type 				Description
		//		book_id						yes				string			ID of the publication.
		//		file							yes				file				Document to be uploaded (like provided by a HTML form file field).
		// Response:
		//		Returns a Publication.
		//		https://developer.calameo.com/content/api/#responsePublication
		//			Name					Type			Description
		//			Code					string		ID of the publication.
		//			Name					string		Title of the publication.
		//			Description		string		Description of the publication.
		//			Category			string		Category.
		//			Format				string		Format.
		//			Dialect				string		Language.
		//			Status				string		Conversion status of the publication. Either QUEUE (waiting to be converted), PROCESS (processing document), STORE (converting document), ERROR (error during convertion) or DONE (publication ready).
		//			IsPrivate			integer		Sends 1 if the publication is private and 0 if not.
		//			AuthID				string		Authentication parameter for private URLs (authid).
		//			AllowMini			integer		Sends 1 if the publication allows access to the miniCalaméo and 0 if not.
		//			Pages					integer		Number of pages of the publication.
		//			Width					integer		Width of a page of the publication.
		//			Height				integer		Height of a page of the publication.
		//			Date					date			Date of citation of the publication.
		//			Creation			datetime	Date of creation of the publication
		//			Modification	datetime	Date of the last modification of the publication.
		//			PictureUrl		string		Absolute URL for the publication's cover
		//			ThumbUrl			string		Absolute URL for the publication's thumbnail.
		//			PublicUrl			string		Absolute URL for the publication's overview.
		//			ViewUrl				string		Absolute URL for the publication's reading page.
		//			CommentsUrl		string		Absolute URL for the publication's comments.
		public function revise (string $book_id, string $file) {
			$fields['action'] = __FUNCTION__;
			$fields['book_id'] = $book_id;
			$fields['file'] = new CURLFile($file, mime_content_type($file), basename($file));

			return json_decode($this->doRequest($fields));
		}

		// Returns config as JSON string
		public function configJSON () {
			return json_encode($this->config, JSON_PRETTY_PRINT);
		}

		// Returns config as associative array
		public function configPHP () {
			return json_decode(json_encode($this->config), true);
		}

		// Returns config as XML string
		public function configXML () {
			$config = new DOMDocument();
			$config->formatOutput = true;
			$root = $config->appendChild($config->createElement('calameoConfig'));
			foreach (json_decode(json_encode($this->config), true) as $item => $value) {
				$root->appendChild($config->createElement($item, $value));
			}
			return $config->saveXML();
		}
	}
