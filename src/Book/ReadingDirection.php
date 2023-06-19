<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class ReadingDirection {
		public const LEFT_TO_RIGHT = 0;
		public const RIGHT_TO_LEFT = 1;

		/**
		 * Reading direction.
		 * - 0 (left-to-right)
		 * - 1 (right-to-left "manga mode")
		*/
		public const DIRECTION = [
			self::LEFT_TO_RIGHT => 'Left to right',
			self::RIGHT_TO_LEFT => 'Right to left',
		];
	}
