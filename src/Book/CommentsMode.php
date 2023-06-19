<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class CommentsMode {
		public const DISABLED              = 0;
		public const MODERATE              = 1;
		public const MODERATE_NOT_CONTACTS = 2;
		public const ACCEPT_CONTACTS       = 3;
		public const ACCEPT                = 4;

		/**
		 * Comments behavior.
		 * - 0 (disabled)
		 * - 1 (moderate all)
		 * - 2 (moderate all except contacts)
		 * - 3 (accept only contacts)
		 * - 4 (accept all)
		*/
		public const COMMENT = [
			self::DISABLED              => 'Disabled',
			self::MODERATE              => 'Moderate all',
			self::MODERATE_NOT_CONTACTS => 'Moderate all except contacts',
			self::ACCEPT_CONTACTS       => 'Accept only contacts',
			self::ACCEPT                 => 'Accept all',
		];
	}
