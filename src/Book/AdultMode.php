<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class AdultMode {
		public const NO  = 0;
		public const YES = 1;

		/**
		 * Restrict access to adults.
		 * - 0 (no)
		 * - 1 (yes)
		*/
		public const ADULT = [
			self::NO  => 'No',
			self::YES => 'Yes',
		];
	}
