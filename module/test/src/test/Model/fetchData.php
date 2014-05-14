<?php
namespace test\Model;
class fetchData {
	public $id, $email, $comment, $created;

	public function exchangeComments( $data ) {
		$this->id = ( !empty( $data['id'] ) ) ? $data['id'] : null;
		$this->email = ( !empty( $data['email'] ) ) ? $data['email'] : null;
		$this->comment = ( !empty( $data['comment'] ) ) ? $data['comment'] : null;
		$this->date = ( !empty( $data['created'] ) ) ? $data['created'] : null;
	}
}