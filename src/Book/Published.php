<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class Published {
		public const DISABLED = 0;
		public const ENABLED  = 1;

		/**
		 * Activation status.
		 * - 0 (disabled)
		 * - 1 (enabled)
		*/
		public const PUBLISHED = [
			self::DISABLED => 'Disabled',
			self::ENABLED  => 'Enabled',
		];
	}
