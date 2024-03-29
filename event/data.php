<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\event;

use Symfony\Contracts\EventDispatcher\Event;

class data extends Event implements \ArrayAccess
{
	private $data;

	public function __construct(array $data = array())
	{
		$this->set_data($data);
	}

	public function set_data(array $data = array())
	{
		$this->data = $data;
	}

	public function get_data()
	{
		return $this->data;
	}

	/**
	 * Returns data filtered to only include specified keys.
	 *
	 * This effectively discards any keys added to data by hooks.
	 */
	public function get_data_filtered($keys)
	{
		return array_intersect_key($this->data, array_flip($keys));
	}

	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * Returns data with updated key in specified offset.
	 *
	 * @param	string	$subarray	Data array subarray
	 * @param	string	$key		Subarray key
	 * @param	mixed	$value		Value to update
	 */
	public function update_subarray($subarray, $key, $value)
	{
		$this->data[$subarray][$key] = $value;
	}
}
