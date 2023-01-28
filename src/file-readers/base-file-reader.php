<?php
/**
 * @author Leonid Vinikov <leonidvinikov@gmail.com>
 */

namespace File_Readers;

use Exception;
use Utils\Args_Object;

abstract class Base_File_Reader implements \ArrayAccess {
	private string $file_path;

	private array $file_stats;

	private mixed $file_handler;

	private Args_Object $args;

	protected bool $is_data_read = false;

	protected int $offset = 0;

	private array $user_data = [];

	public function __construct( $file_path, $args = [] ) {
		$this->file_path = $file_path;

		$class_name = $this->get_args_class();

		$this->args = new $class_name( $args );

		/** @noinspection PhpConditionAlreadyCheckedInspection */
		if ( ! ( $this->args instanceof Args_Object ) ) {
			throw new Exception( 'The class returned by `get_args_class()` must be an instance of `Args_Object`.' );
		}
	}

	public function open(): bool {
		$this->file_handler = fopen( $this->file_path, 'rb' );

		if ( ! $this->file_handler ) {
			return false;
		}

		if ( ! $this->validate_header() ) {
			throw new Exception( 'Invalid file header.' );
		}

		$this->file_stats = fstat( $this->file_handler );

		$this->seek( 0 );

		return (bool) $this->file_handler;
	}

	public function close(): bool {
		return fclose( $this->file_handler );
	}

	public function get_data(): array {
		$this->ensure_file_read();

		if ( ! empty( $this->user_data ) ) {
			return $this->user_data;
		}

		$this->user_data = $this->get_user_data();

		return $this->user_data;
	}

	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ): bool {
		$this->ensure_data_get();

		return isset( $this->user_data[ $offset ] );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		$this->ensure_data_get();

		return $this->user_data[ $offset ];
	}

	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->method_is_read_only();
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		$this->method_is_read_only();
	}

	abstract public function get_file_extension(): string;

	abstract public function read_data(): void;

	protected function read( $length ): mixed {
		$this->offset += $length;

		return fread( $this->file_handler, $length );
	}

	protected function seek( $offset, $whence = SEEK_SET ): int {
		$this->offset = $offset;
		return fseek( $this->file_handler, $offset, $whence );
	}

	protected function get_file_size(): int {
		return $this->file_stats['size'];
	}

	protected function get_args(): Args_Object {
		return $this->args;
	}

	protected function read_uint(): int {
		return unpack( 'I', $this->read( 4 ) )[1];
	}

	protected function read_ushort(): int {
		return unpack( 'S', $this->read( 2 ) )[1];
	}

	protected function read_int(): int {
		return unpack( 'i', $this->read( 4 ) )[1];
	}

	protected function read_bytes( $length ): array {
		$bytes = $this->read( $length );

		return unpack( "C$length", $bytes );
	}

	abstract protected function get_args_class(): string;

	abstract protected function get_user_data(): array;

	abstract protected function validate_header(): bool;

	private function ensure_file_read() {
		if ( ! $this->is_data_read ) {
			throw new Exception( 'The file info must be read before getting the data (`$this->is_info_read = false`).' );
		}
	}

	private function ensure_data_get() {
		if ( empty( $this->user_data) ) {
			throw new Exception( 'The data must be get before getting the actual data use `$instance->get_data()`.' );
		}
	}

	private function method_is_read_only() {
		$class = static::class;
		throw new Exception( "'$class' is read only'" );
	}
}
