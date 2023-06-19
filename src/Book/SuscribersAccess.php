<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class SuscribersAccess {
		public const DISABLED = 0;
		public const ENABLED  = 1;

		/**
		 * Allow subscribers access.
		 * - 0 (disabled)
		 * - 1 (enabled)
		*/
		public const SUBSCRIBE = [
			self::DISABLED => 'Disabled',
			self::ENABLED  => 'Enabled',
		];
	}
