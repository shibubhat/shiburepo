<?php 

class Grabber extends CI_Model {

    var $title   = '';
    var $content = '';
    var $date    = '';

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_last_ten_entries()
    {
        $query = $this->db->get('entries', 10);
        return $query->result();
    }

    function insert_labels($data)
    {
        $this->db->insert_batch('label', $data);
    } function insert_messages($data)
    {
        $this->db->insert('message', $data);
    }
	function insert_labelmessage($data)
    {
        $this->db->insert('messagelabel', $data);
    }
	function update_label($id,$ts)
    {
	    $this->db->update('label', $ts, array('id' =>$id));
        //$this->db->update('messagelabel', $data);
    }
	
	function get_labels(){
		return $this->db->get('label')->result_array();
	}
	
	function check_label_exist($label){
		$this->db->like('name', $label,'none'); 
		$query=$this->db->get('label');
		return $query->num_rows();
	}

    function update_entry()
    {
        $this->title   = $_POST['title'];
        $this->content = $_POST['content'];
        $this->date    = time();

        $this->db->update('entries', $this, array('id' => $_POST['id']));
    }

}

?>