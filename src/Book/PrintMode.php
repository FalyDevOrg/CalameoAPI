<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class PrintMode {
		public const DISABLED = 0;
		public const CONTACTS = 1;
		public const EVERYONE = 2;

		/**
		 * Print behavior.
		 * - 0 (disabled)
		 * - 1 (only contacts)
		 * - 2 (everyone)
		*/
		public const PRINT = [
			self::DISABLED => 'Disabled',
			self::CONTACTS => 'Only contacts',
			self::EVERYONE => 'Everyone',
		];
	}
