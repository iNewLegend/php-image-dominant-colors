<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 *
 * Inspired by: https://developer.wordpress.org/reference/functions/wp_parse_args/
 */

namespace Utils;

abstract class Args_Object implements \ArrayAccess, \IteratorAggregate, \Countable {
	private \ArrayObject $args;

	public function __construct( array $args ) {
		$this->set_args( $args );
	}

	private function set_args( array $args ): void {
		$args = array_merge( $this->get_default_args(), $args );

		$this->args = new \ArrayObject( $args );
	}

	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ): bool {
		return isset( $this->args[ $offset ] );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->args[ $offset ];
	}

	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ): void {
		$this->args[ $offset ] = $value;
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ): void {
		$this->args[ $offset ] = null;
	}

	public function getIterator(): \ArrayIterator {
		return $this->args->getIterator();
	}

	public function count(): int {
		return $this->args->count();
	}

	abstract protected function get_default_args(): array;
}
