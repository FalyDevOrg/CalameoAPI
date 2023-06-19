<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class ViewMode {
		public const BOOK   = 'book';
		public const SLIDE  = 'slide';
		public const SCROLL = 'scroll';

		/**
		 * Default viewing mode.
		 * - book
		 * - slide
		 * - scroll
		*/
		public const VIEW = [
			self::BOOK   => 'Book',
			self::SLIDE  => 'Slide',
			self::SCROLL => 'Scroll',
		];
	}
