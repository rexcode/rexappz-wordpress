<?php

/**
 * Git commit tree.
 */
class WordPress_GitHub_Sync_Tree implements Iterator {

	/**
	 * Whether the tree has changed.
	 *
	 * @var bool
	 */
	protected $changed = false;

	/**
	 * @var WordPress_GitHub_Sync_Api
	 */
	protected $api;

	/**
	 * Current tree if retrieved, otherwise, error
	 *
	 * @var array|WP_Error
	 */
	protected $tree;

	/**
	 * Current position in the loop.
	 *
	 * @var int
	 */
	protected $position;

	/**
	 * Current blob in the loop.
	 *
	 * @var stdClass
	 */
	protected $current;

	/**
	 * Fetches the current tree from GitHub.
	 */
	public function __construct() {
		$this->api  = new WordPress_GitHub_Sync_Api;
		$this->tree = new WP_Error( 'no_tree', __( 'Tree not initialized', 'wordpress-github-sync' ) );
	}

	/**
	 * Fetch the last tree from the repository.
	 */
	public function fetch_last() {
		$this->tree = $this->api->last_tree_recursive();
	}

	/**
	 * Fetch the tree for the provided sha from the repository.
	 *
	 * @param $sha
	 */
	public function fetch_sha( $sha ) {
		$this->tree = $this->api->get_tree_recursive( $sha );
	}

	/**
	 * Checks if the tree is currently ready.
	 *
	 * This will return false if the initial fetch of the tree
	 * returned an error of some sort.
	 *
	 * @return bool
	 */
	public function is_ready() {
		if ( is_wp_error( $this->tree ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the error caused when fetching the tree.
	 *
	 * @return string
	 */
	public function last_error() {
		return $this->tree->get_error_message();
	}

	/**
	 * Manipulates the tree for a given post.
	 *
	 * If remove is true, removes the provided post from the current true.
	 * If false or nothing is provided, adds or updates the tree
	 * with the provided post.
	 *
	 * @param WordPress_GitHub_Sync_Post $post
	 * @param bool $remove
	 */
	public function post_to_tree( $post, $remove = false ) {
		$match = false;

		foreach ( $this->tree as $index => $blob ) {
			if ( ! isset( $blob->sha ) ) {
				continue;
			}

			if ( $blob->sha === $post->sha() ) {
				unset( $this->tree[ $index ] );
				$match = true;

				if ( ! $remove ) {
					$this->tree[] = $this->new_blob( $post, $blob );
				} else {
					$this->changed = true;
				}

				break;
			}
		}

		if ( ! $match && ! $remove ) {
			$this->tree[]  = $this->new_blob( $post );
			$this->changed = true;
		}
	}

	/**
	 * Combines a post and (potentially) a blob.
	 *
	 * If no blob is provided, turns post into blob.
	 *
	 * If blob is provided, compares blob to post
	 * and updates blob data based on differences.
	 *
	 * @param WordPress_GitHub_Sync_Post $post
	 * @param bool|stdClass $blob
	 *
	 * @return array
	 */
	public function new_blob( $post, $blob = false ) {
		if ( ! $blob ) {
			$blob = $this->blob_from_post( $post );
		} else {
			unset( $blob->url );
			unset( $blob->size );

			if ( $blob->path !== $post->github_path() ) {
				$blob->path    = $post->github_path();
				$this->changed = true;
			}

			$blob_data = $this->api->get_blob( $blob->sha );

			if ( base64_decode( $blob_data->content ) !== $post->github_content() ) {
				unset( $blob->sha );
				$blob->content = $post->github_content();
				$this->changed = true;
			}
		}

		return $blob;
	}

	/**
	 * Creates a blob with the data required for the tree.
	 *
	 * @param WordPress_GitHub_Sync_Post $post
	 *
	 * @return stdClass
	 */
	public function blob_from_post( $post ) {
		$blob = new stdClass;

		$blob->path    = $post->github_path();
		$blob->mode    = '100644';
		$blob->type    = 'blob';
		$blob->content = $post->github_content();

		return $blob;
	}

	/**
	 * Retrieves a tree blob for a given path.
	 *
	 * @param string $path
	 *
	 * @return bool|stdClass
	 */
	public function get_blob_for_path( $path ) {
		foreach ( $this->tree as $blob ) {
			// this might be a problem if the filename changed since it was set
			// (i.e. post updated in middle mass export)
			// solution?
			if ( $path === $blob->path ) {
				return $blob;
			}
		}

		return false;
	}

	/**
	 * Exports the tree as a new commit with a provided commit message.
	 *
	 * @param string $msg
	 *
	 * @return bool|WP_Error false if unchanged, true if success, WP_Error if error
	 */
	public function export( $msg ) {
		if ( ! $this->changed ) {
			return false;
		}

		WordPress_GitHub_Sync::write_log( __( 'Creating the tree.', 'wordpress-github-sync' ) );
		$tree = $this->api->create_tree( array_values( $this->tree ) );

		if ( is_wp_error( $tree ) ) {
			return $tree;
		}

		WordPress_GitHub_Sync::write_log( __( 'Creating the commit.', 'wordpress-github-sync' ) );
		$commit = $this->api->create_commit( $tree->sha, $msg );

		if ( is_wp_error( $commit ) ) {
			return $commit;
		}

		WordPress_GitHub_Sync::write_log( __( 'Setting the master branch to our new commit.', 'wordpress-github-sync' ) );
		$ref = $this->api->set_ref( $commit->sha );

		if ( is_wp_error( $ref ) ) {
			return $ref;
		}

		return true;
	}

	/**
	 * Return the current element.
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return stdClass
	 */
	public function current() {
		return $this->current;
	}

	/**
	 * Move forward to next element.
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 */
	public function next() {
		$this->position++;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return int|null int on success, or null on failure.
	 */
	public function key() {
		if ( $this->valid() ) {
			return $this->position;
		}

		return null;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean true on success, false on failure.
	 */
	public function valid() {
		global $wpdb;

		while ( isset( $this->tree[ $this->position ] ) ) {
			$blob = $this->tree[ $this->position ];

			// Skip the repo's readme
			if ( 'readme' === strtolower( substr( $blob->path, 0, 6 ) ) ) {
				WordPress_GitHub_Sync::write_log( __( 'Skipping README', 'wordpress-github-sync' ) );
				$this->next();

				continue;
			}

			// If the blob sha already matches a post, then move on
			$id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sha' AND meta_value = '$blob->sha'" );
			if ( $id ) {
				WordPress_GitHub_Sync::write_log(
					sprintf(
						__( 'Already synced blob %s', 'wordpress-github-sync' ),
						$blob->path
					)
				);
				$this->next();

				continue;
			}

			$blob = $this->api->get_blob( $blob->sha );

			if ( is_wp_error( $blob ) ) {
				WordPress_GitHub_Sync::write_log(
					sprintf(
						__( 'Failed getting blob with error: %s', 'wordpress-github-sync' ),
						$blob->get_error_message()
					)
				);
				$this->next();

				continue;
			}

			$content = base64_decode( $blob->content );

			// If it doesn't have YAML frontmatter, then move on
			if ( '---' !== substr( $content, 0, 3 ) ) {
				WordPress_GitHub_Sync::write_log(
					sprintf(
						__( 'No front matter on blob %s', 'wordpress-github-sync' ),
						$blob->sha
					)
				);
				$this->next();

				continue;
			}

			$blob->content = $content;
			$this->current = $blob;

			return true;
		}

		return false;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 */
	public function rewind() {
		$this->position = 0;
	}
}
