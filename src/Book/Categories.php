<?php
  declare(strict_types=1);

	namespace Fawno\Calameo\Book;

	/**
	 * Category (references)
	 * - http://help.calameo.com/index.php?title=API:Category_(references)
	 *
	 * @package Fawno\Calameo\Book
	 */
	class Categories {
		public const DESIGN     = 'DESIGN';
		public const BUSINESS   = 'BUSINESS';
		public const AUTO       = 'AUTO';
		public const CULTURE    = 'CULTURE';
		public const SCHOOL     = 'SCHOOL';
		public const HEALTH     = 'HEALTH';
		public const HISTORY    = 'HISTORY';
		public const HUMOR      = 'HUMOR';
		public const LAW        = 'LAW';
		public const LITERATURE = 'LITERATURE';
		public const MISC       = 'MISC.';
		public const MOVIES     = 'MOVIES';
		public const MUSIC      = 'MUSIC';
		public const NATURE     = 'NATURE';
		public const NEWS       = 'NEWS';
		public const POLITICS   = 'POLITICS';
		public const RELIGION   = 'RELIGION';
		public const SCIENCES   = 'SCIENCES';
		public const SEXY       = 'SEXY';
		public const PEOPLE     = 'PEOPLE';
		public const SPORTS     = 'SPORTS';
		public const TECH       = 'TECH';
		public const TRAVEL     = 'TRAVEL';
		public const VIDEOGAMES = 'VIDEOGAMES';

		/**
		 * Category (references)
		 * http://help.calameo.com/index.php?title=API:Category_(references)
		*/
		public const CATEGORIES = [
			self::DESIGN     => 'Arts & Design',
			self::BUSINESS   => 'Business',
			self::AUTO       => 'Cars',
			self::CULTURE    => 'Culture',
			self::SCHOOL     => 'Education',
			self::HEALTH     => 'Health',
			self::HISTORY    => 'History',
			self::HUMOR      => 'Humor',
			self::LAW        => 'Law',
			self::LITERATURE => 'Literature',
			self::MISC       => 'Misc.',
			self::MOVIES     => 'Movies',
			self::MUSIC      => 'Music',
			self::NATURE     => 'Nature',
			self::NEWS       => 'News',
			self::POLITICS   => 'Politics',
			self::RELIGION   => 'Religion',
			self::SCIENCES   => 'Sciences',
			self::SEXY       => 'Sexy',
			self::PEOPLE     => 'Society',
			self::SPORTS     => 'Sports',
			self::TECH       => 'Technology',
			self::TRAVEL     => 'Travels',
			self::VIDEOGAMES => 'Videogames',
		];
	}
