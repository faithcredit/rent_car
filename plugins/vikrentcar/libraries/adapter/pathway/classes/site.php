<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.application
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.pathway.pathway');

/**
 * Class to maintain a pathway for the site client.
 * The user's navigated path within the site application.
 *
 * @since  10.1.19
 */
class JPathwaySite extends JPathway
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $options  The class options.
	 */
	public function __construct($options = array())
	{
		// add home to pathway
		$this->addItem(__('Home'), 'index.php');

		// extract post from current URL
		$id   = url_to_postid(JUri::current());
		$post = get_post($id);

		if ($post)
		{
			$tree = array($post);

			// get post parent
			$post->post_parent;

			// iterate as long as we have a parent ID
			while ($post && $post->post_parent)
			{
				// get parent
				$post = get_post($post->post_parent);

				if ($post)
				{
					// prepend parent
					array_unshift($tree, $post);
				}
			}

			// get regex to extract shortcodes from post content
			$regex = get_shortcode_regex();

			// build tree
			foreach ($tree as $post)
			{
				$parts = array();

				if (preg_match("/$regex/s", $post->post_content, $match))
				{
					// search for the component name
					if (isset($match[2]))
					{
						$parts['option'] = 'com_' . $match[2];
					}

					// search for shortcode attributes
					if (isset($match[3]))
					{
						// extract key and values from shortcode attributes
						if (preg_match_all("/([a-z0-9_\-]+)=\"([^\"]*)\"/si", $match[3], $chunks))
						{	
							// iterate chunks
							for ($i = 0; $i < count($chunks[0]); $i++)
							{
								// append key=val to $parts
								$parts[trim($chunks[1][$i])] = $chunks[2][$i];
							}
						}
					}
				}

				// build plain link
				$link = 'index.php?' . http_build_query(array_filter($parts));

				// add post within the pathway
				$this->addItem($post->post_title, $link);
			}
		}
	}
}
