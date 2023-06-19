<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	class PublishingMode {
		public const PUBLIC  = 1;
		public const PRIVATE = 2;

		/**
		 * Access to the publication.
		 * - 1 (public)
		 * - 2 (private)
		*/
		public const PUBLISHING_MODE = [
			self::PUBLIC  => 'Public',
			self::PRIVATE => 'Private',
		];
	}
