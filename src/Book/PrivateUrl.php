<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class PrivateUrl {
		public const DISABLED = 0;
		public const ENABLED  = 1;

		/**
		 * Use a private URL.
		 * - 0 (disabled)
		 * - 1 (enabled)
		*/
		public const PRIVATE_URL = [
			self::DISABLED => 'Disabled',
			self::ENABLED  => 'Enabled',
		];
	}
