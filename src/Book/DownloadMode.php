<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class DownloadMode {
		public const DISABLED = 0;
		public const CONTACTS = 1;
		public const EVERYONE = 2;

		/**
		 * Download behavior.
		 * - 0 (disabled)
		 * - 1 (only contacts)
		 * - 2 (everyone)
		*/
		public const DOWNLOAD = [
			self::DISABLED => 'Disabled',
			self::CONTACTS => 'Only contacts',
			self::EVERYONE => 'Everyone',
		];
	}
