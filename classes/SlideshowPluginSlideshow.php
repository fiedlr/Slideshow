<?php
/**
 * SlideshowPluginSlideshow creates a post type specifically designed for
 * slideshows and their individual settings
 *
 * @since 1.0.0
 * @author: Stefan Boonstra
 */
class SlideshowPluginSlideshow extends SlideshowPluginModel
{
	/** @var string */
	static $postType = 'slideshow';

	/** @var array */
	static $postMetaDefaults = array(
		'_slideshow_jquery_image_gallery_slides'           => array(),
		'_slideshow_jquery_image_gallery_style'            => -1,
		'_slideshow_jquery_image_gallery_settings_profile' => -1,
	);

	/**
	 * Registers class with the slideshow's post type class.
	 */
	static function init()
	{
		global $wp_version;

		add_action('admin_menu'           , array(__CLASS__, 'modifyAdminMenu'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'localizeScript'), 11);

		SlideshowPluginPostType::registerPostType(
			__CLASS__,
			self::$postType,
			array(
				'labels'               => array(
					'name'               => __('Slideshows', 'slideshow-plugin'),
					'singular_name'      => __('Slideshow', 'slideshow-plugin'),
					'menu_name'          => __('Slideshows', 'slideshow-plugin'),
					'name_admin_bar'     => __('Slideshows', 'slideshow-plugin'),
					'add_new'            => __('Add New', 'slideshow-plugin'),
					'add_new_item'       => __('Add New Slideshow', 'slideshow-plugin'),
					'new_item'           => __('New Slideshow', 'slideshow-plugin'),
					'edit_item'          => __('Edit slideshow', 'slideshow-plugin'),
					'view_item'          => __('View slideshow', 'slideshow-plugin'),
					'all_items'          => __('All Slideshows', 'slideshow-plugin'),
					'search_items'       => __('Search Slideshows', 'slideshow-plugin'),
					'parent_item_colon'  => __('Parent Slideshows:', 'slideshow-plugin'),
					'not_found'          => __('No slideshows found', 'slideshow-plugin'),
					'not_found_in_trash' => __('No slideshows found', 'slideshow-plugin')
				),
				'public'               => false,
				'publicly_queryable'   => false,
				'show_ui'              => true,
				'show_in_menu'         => true,
				'query_var'            => true,
				'rewrite'              => true,
				'capability_type'      => 'post',
				'capabilities'         => array(
					'edit_post'              => SlideshowPluginGeneralSettings::$capabilities['editSlideshows'],
					'read_post'              => SlideshowPluginGeneralSettings::$capabilities['addSlideshows'],
					'delete_post'            => SlideshowPluginGeneralSettings::$capabilities['deleteSlideshows'],
					'edit_posts'             => SlideshowPluginGeneralSettings::$capabilities['editSlideshows'],
					'edit_others_posts'      => SlideshowPluginGeneralSettings::$capabilities['editSlideshows'],
					'publish_posts'          => SlideshowPluginGeneralSettings::$capabilities['addSlideshows'],
					'read_private_posts'     => SlideshowPluginGeneralSettings::$capabilities['editSlideshows'],

					'read'                   => SlideshowPluginGeneralSettings::$capabilities['addSlideshows'],
					'delete_posts'           => SlideshowPluginGeneralSettings::$capabilities['deleteSlideshows'],
					'delete_private_posts'   => SlideshowPluginGeneralSettings::$capabilities['deleteSlideshows'],
					'delete_published_posts' => SlideshowPluginGeneralSettings::$capabilities['deleteSlideshows'],
					'delete_others_posts'    => SlideshowPluginGeneralSettings::$capabilities['deleteSlideshows'],
					'edit_private_posts'     => SlideshowPluginGeneralSettings::$capabilities['editSlideshows'],
					'edit_published_posts'   => SlideshowPluginGeneralSettings::$capabilities['editSlideshows'],
				),
				'has_archive'          => true,
				'hierarchical'         => false,
				'menu_position'        => null,
				'menu_icon'            => version_compare($wp_version, '3.8', '<') ? SlideshowPluginMain::getPluginUrl() . '/images/' . __CLASS__ . '/adminIcon.png' : 'dashicons-format-gallery',
				'supports'             => array('title'),
			),
			array(
				'_slideshow_jquery_image_gallery_information'      => array(
					'dataType'      => null,
					'title'         => __('Information', 'slideshow-plugin'),
					'callback'      => array(__CLASS__, 'informationMetaBox'),
					'screen'        => self::$postType,
					'context'       => 'normal',
					'priority'      => 'high',
					'callback_args' => null,
				),
				'_slideshow_jquery_image_gallery_slides'           => array(
					'dataType'      => 'array',
					'title'         => __('Slides', 'slideshow-plugin'),
					'callback'      => array(__CLASS__, 'slidesMetaBox'),
					'screen'        => self::$postType,
					'context'       => 'advanced',
					'priority'      => 'default',
					'callback_args' => null,
				),
				'_slideshow_jquery_image_gallery_style'            => array(
					'dataType'      => 'int',
					'title'         => __('Style', 'slideshow-plugin'),
					'callback'      => array(__CLASS__, 'styleMetaBox'),
					'screen'        => self::$postType,
					'context'       => 'side',
					'priority'      => 'default',
					'callback_args' => null,
				),
				'_slideshow_jquery_image_gallery_settings_profile' => array(
					'dataType'      => 'int',
					'title'         => __('Settings Profile', 'slideshow-plugin'),
					'callback'      => array(__CLASS__, 'settingsMetaBox'),
					'screen'        => self::$postType,
					'context'       => 'side',
					'priority'      => 'default',
					'callback_args' => null,
				),
			)
		);
	}

	/**
	 * @see SlideshowPluginModel::__construct
	 * @param int|null|WP_Post $post
	 */
	function __construct($post)
	{
		$this->modelPostType = self::$postType;

		parent::__construct($post);
	}

	/**
	 * Get default post meta by the passed key.
	 *
	 * @since 2.3.0
	 * @see SlideshowPluginModel::getPostMetaDefaults
	 */
	function getPostMetaDefaults($key)
	{
		if (isset(self::$postMetaDefaults[$key]))
		{
			return self::$postMetaDefaults[$key];
		}

		return null;
	}

	/**
	 * Shows the support plugin message
	 *
	 * @since 2.0.0
	 */
	static function supportPluginMessage()
	{
		// TODO Show support message on edit slideshow
//		// Add support plugin message on edit slideshow
//		if (isset($_GET['action']) &&
//			strtolower($_GET['action']) == strtolower('edit'))
//		{
//			add_action('admin_notices', array(__CLASS__,  'supportPluginMessage'));
//		}

		include SlideshowPluginMain::getPluginPath() . '/views/' . __CLASS__ . '/support-plugin.php';
	}

	/**
	 * Shows some information about this slideshow
	 *
	 * @since 1.0.0
	 */
	static function informationMetaBox()
	{
		global $post;

		$snippet   = htmlentities(sprintf('<?php do_action(\'slideshow_deploy\', \'%s\'); ?>', $post->ID));
		$shortCode = htmlentities(sprintf('[' . SlideshowPluginShortcode::$shortCode . ' id=\'%s\']', $post->ID));

		include SlideshowPluginMain::getPluginPath() . '/views/' . __CLASS__ . '/information.php';
	}

	/**
	 * Shows slides currently in slideshow
	 *
	 * TODO Tidy up, it's probably best to move all to 'slides.php'
	 *
	 * @since 1.0.0
	 */
	static function slidesMetaBox()
	{
		global $post;

		// Get views
		$views = SlideshowPluginSlideshowSettingsHandler::getViews($post->ID);

		// Insert slide buttons
		echo '<p style="text-align: center;">
			<i>' . __('Insert', 'slideshow-plugin') . ':</i><br/>' .
			SlideshowPluginSlideInserter::getImageSlideInsertButton() .
			SlideshowPluginSlideInserter::getTextSlideInsertButton() .
			SlideshowPluginSlideInserter::getVideoSlideInsertButton() .
			'</p>';

		// Toggle slides open/closed
		echo '<p style="text-align: center;">
			<a href="#" class="open-slides-button">' . __( 'Open all', 'slideshow-plugin' ) . '</a>
			|
			<a href="#" class="close-slides-button">' . __( 'Close all', 'slideshow-plugin' ) . '</a>
		</p>';

		// No views/slides message
		if (count($views) <= 0)
		{
			echo '<p>' . __('Add slides to this slideshow by using one of the buttons above.', 'slideshow-plugin') . '</p>';
		}

		// Start list
		echo '<div class="sortable-slides-list">';

		// Print views
		if (is_array($views))
		{
			foreach($views as $view)
			{
				if (!($view instanceof SlideshowPluginSlideshowView))
				{
					continue;
				}

				echo $view->toBackEndHTML();
			}
		}

		// End list
		echo '</div>';

		// Templates
		SlideshowPluginSlideshowSlide::getBackEndTemplates(false);
	}

	/**
	 * Shows style used for slideshow
	 *
	 * TODO Improve styling for usage in sidebar.
	 *
	 * @since 1.3.0
	 */
	static function styleMetaBox()
	{
		echo 'Placeholder for styles dropdown';

		echo '<br /><br />Add "edit style" link';

//		global $post;
//
//		// Get settings
//		$settings = SlideshowPluginSlideshowSettingsHandler::getStyleSettings($post->ID, true);
//
//		// Include style settings file
//		include SlideshowPluginMain::getPluginPath() . '/views/' . __CLASS__ . '/style-settings.php';
	}

	/**
	 * Shows settings for particular slideshow
	 *
	 * TODO Implement.
	 *
	 * @since 1.0.0
	 */
	static function settingsMetaBox()
	{
//		global $post;

		$postTypeInformation = SlideshowPluginPostType::getPostTypeInformation(self::$postType);

		wp_nonce_field($postTypeInformation['nonceAction'], $postTypeInformation['nonceName']);

		echo 'Placeholder for settings profiles dropdown';

		echo '<br /><br />Add "edit settings profile" link';

//		// Get settings
//		$settings = SlideshowPluginSlideshowSettingsHandler::getSettings($post->ID, true);
//
//		// Include
//		include SlideshowPluginMain::getPluginPath() . '/views/' . __CLASS__ . '/settings.php';
	}

	/**
	 * Modifies the admin menu, removing the "Add New" link from the slideshow menu.
	 *
	 * @since 2.3.0
	 */
	static function modifyAdminMenu()
	{
		global $submenu;

		unset($submenu['edit.php?post_type=' . self::$postType][10]);
	}

	/**
	 * Enqueues styles and scripts.
	 *
	 * @since 2.3.0
	 */
	static function localizeScript()
	{
		if (SlideshowPluginMain::getCurrentPostType() != self::$postType)
		{
			return;
		}

		wp_localize_script(
			'slideshow-jquery-image-gallery-backend-script',
			'slideshow_jquery_image_gallery_backend_script_slideshow',
			array(
				'data' => array(),
				'localization' => array(
					'confirm'       => __('Are you sure you want to delete this slide?', 'slideshow-plugin'),
					'uploaderTitle' => __('Insert image slide', 'slideshow-plugin')
				)
			)
		);
	}
}