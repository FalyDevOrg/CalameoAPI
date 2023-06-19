<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	/**
	 * Format (references)
	 * - http://help.calameo.com/index.php?title=API:Format_(references)
	 *
	 * @package Fawno\Calameo\Book
	 */
	class Formats {
		public const ALBUMS        = 'ALBUMS';
		public const BD            = 'BD';
		public const BOOKS         = 'BOOKS';
		public const BROCHURES     = 'BROCHURES';
		public const CATALOGS      = 'CATALOGS';
		public const COMICS        = 'COMICS';
		public const MAGAZINES     = 'MAGAZINES';
		public const MANGAS        = 'MANGAS';
		public const MANUALS       = 'MANUALS';
		public const MISC          = 'MISC';
		public const MULTIMEDIA    = 'MULTIMEDIA';
		public const NEWSPAPERS    = 'NEWSPAPERS';
		public const NOVELS        = 'NOVELS';
		public const PRESENTATIONS = 'PRESENTATIONS';
		public const REPORTS       = 'REPORTS';
		public const SHEETMUSIC    = 'SHEETMUSIC';

		/**
		 * Format (references)
		 * http://help.calameo.com/index.php?title=API:Format_(references)
		*/
		public const FORMATS = [
			self::ALBUMS        => 'Albums',
			self::BD            => 'B.D.',
			self::BOOKS         => 'Books',
			self::BROCHURES     => 'Brochures',
			self::CATALOGS      => 'Catalogs',
			self::COMICS        => 'Comics',
			self::MAGAZINES     => 'Magazines',
			self::MANGAS        => 'Mangas',
			self::MANUALS       => 'Manuals',
			self::MISC          => 'Misc',
			self::MULTIMEDIA    => 'Mutlimedia',
			self::NEWSPAPERS    => 'Newspapers',
			self::NOVELS        => 'Novels',
			self::PRESENTATIONS => 'Presentations',
			self::REPORTS       => 'Reports',
			self::SHEETMUSIC    => 'Sheet music',
		];
	}
