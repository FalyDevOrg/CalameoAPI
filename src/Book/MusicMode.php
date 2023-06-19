<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class MusicMode {
		public const LOOP = 0;
		public const ONCE = 1;

		/**
		 * Background music mode.
		 * - 0 (loop forever)
		 * - 1 (play only once)
		*/
		public const MUSIC = [
			self::LOOP => 'Loop forever',
			self::ONCE => 'Play only once',
		];
	}
