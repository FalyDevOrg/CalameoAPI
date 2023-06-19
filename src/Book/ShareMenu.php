<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class ShareMenu {
		public const DISABLED = 0;
		public const ENABLED  = 1;

		/**
		 * Share menu.
		 * - 0 (disabled)
		 * - 1 (enabled)
		 * Enabled by default.
		*/
		public const SHARE = [
			self::DISABLED => 'Disabled',
			self::ENABLED  => 'Enabled',
		];
	}
