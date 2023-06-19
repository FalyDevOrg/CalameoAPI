<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class SfxMode {
		public const DISABLED = 0;
		public const ENABLED  = 1;

		/**
		 * Play sound effects like page flipping.
		 * - 0 (disabled)
		 * - 1 (enabled)
		*/
		public const SFX = [
			self::DISABLED => 'Disabled',
			self::ENABLED  => 'Enabled',
		];
	}
