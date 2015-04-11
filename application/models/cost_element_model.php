<?php

class Cost_element_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_cost_element_template() {
        $this->db->select('quotation_cost_element_template.*,organisation_structure.structure_name,position_level.name');
        $this->db->from('quotation_cost_element_template');
        $this->db->join('organisation_structure', 'organisation_structure.id_organisation_structure=quotation_cost_element_template.structure_org_id');
        $this->db->join('position_level', 'position_level.id_position_level=quotation_cost_element_template.level_employee_id');
        //$this->db->where_in('quotation_cost_element_template.quotation_cost_element_template_id',$id_quotation);
        return $this->db->get()->result_array();
    }

    function get_cost_element_detail_template() {
        return $this->db->get('quotation_cost_element_detail_template')->result_array();
    }

    public function get_cost_element_all()
    {
        $this->db->select('*');
        $this->db->from('cost_element');

        return $this->db->get()->result_array();
    }

    public function get_cost_element_by_id($id)
    {
        $this->db->select('*');
        $this->db->from('cost_element');

        $this->db->where('id_cost_element', $id);

        return $this->db->get()->result_array();
    }


    public function save_cost_element($data)
    {
        $this->db->trans_start();

        $d['name'] = $data['name'];
        $d['description'] = $data['description'];

        $this->db->insert('cost_element', $d);

        $ce_id = $this->db->insert_id();
        $this->add_detail_cost_element($ce_id, $data['detail_cost_element']);

        $this->db->trans_complete();
    }

    public function edit_cost_element($data)
    {
        $this->db->trans_start();

        $d['name'] = $data['name'];
        $d['description'] = $data['description'];

        $this->db->where('id_cost_element', $data['id_cost_element']);
        $this->db->update('cost_element', $d);

        $ce_id = $data['id_cost_element'];

        $this->delete_detail_cost_element($ce_id);
        $this->add_detail_cost_element($ce_id, $data['detail_cost_element']);

        $this->db->trans_complete();
    }

    public function delete_detail_cost_element($ce)
    {
        $this->db->where('cost_element', $ce);
        $this->db->delete('detail_cost_element');
    }

    public function add_detail_cost_element($id, $data)
    {
        $i = 0;
        foreach($data as $d)
        {
            $dinput = array();
            $dinput['name'] = $d['name'];
            $dinput['value_type'] = $d['value_type'];
            $dinput['element_category'] = $d['element_category'];
            $dinput['sequence_operation'] = $d['sequence_operation'];
            $dinput['value'] = $d['value'];
            $dinput['cost_element'] = $id;
            $dinput['condition'] = $d['condition'];
            $dinput['occurrence'] = $d['occurrence'];
            $dinput['invoiceable'] = $d['invoiceable'];
            $dinput['salariable'] = $d['salariable'];
            $dinput['index'] = $i;

            $this->db->insert('detail_cost_element', $dinput);
            $i++;
        }
    }

    public function get_cost_element_detail_ce($id)
    {
        $this->db->select('*');
        $this->db->from('detail_cost_element');
        $this->db->where('cost_element', $id);
        $this->db->order_by('index', 'asc');

        return $this->db->get()->result_array();
    }

    public function calculate_invoice($occurrence, $ce)
    {
        //get data
        $dce = $this->get_cost_element_detail_ce($ce);
        $return_val = array();
        $return_val['id_cost_element'] = $ce;
        $return_val['detail_calculation'] = array();
        $temp_grid = array();
        $aggregate = 0;
        switch($occurrence)
        {
            case 'per_month':
                foreach($dce as $e)
                {

                    if($e['sequence_operation'] != 'skip' && $e['occurrence'] == 'per_month' && $e['invoiceable'] == 1)
                    {
                        $data_input = array();
                        $data_input['name'] = $e['name'];
                        $data_input['value'] = $e['value'];
                        switch($e['sequence_operation'])
                        {
                            case 'sum':
                                $value = $e['value'] * 1.0;
                                $data_input['calc_value'] = $value;
                                $aggregate += $value;
                                break;
                            case 'multply_sum':
                                if($e['condition'] != '' && $e['condition'] != null)
                                {
                                    $condition = json_decode($e['condition'], true);
                                    if(isset($condition['previous_value']) && $condition['previous_value'] != null)
                                    {
                                        $prev_value = $condition['previous_value'];
                                        for($i=0;$i<count($prev_value);$i++)
                                        {
                                            for($j=0;$j<count($temp_grid);$j++)
                                            {
                                                if($temp_grid[$j]['name'] == $prev_value[$i])
                                                {
                                                    $calc_value = $temp_grid[$j]['calc_value'] * 1.0;
                                                    $value = $e['value'] * 1.0;
                                                    $val = $calc_value * $value;
                                                    $data_input['calc_value'] = $val;
                                                    $aggregate += $val;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $value = $e['value'] * 1.0;
                                        $val = $aggregate * $value;
                                        $data_input['calc_value'] = $val;
                                        $aggregate += $val;
                                    }
                                }
                                else
                                {
                                    $value = $e['value'] * 1.0;
                                    $val = $aggregate * $value;
                                    $data_input['calc_value'] = $val;
                                    $aggregate += $val;
                                }
                                break;
                        }
                        $data_input['aggregate'] = $aggregate;
                        array_push($temp_grid, $data_input);
                        array_push($return_val['detail_calculation'], $data_input);
                        //echo json_encode($return_val);
                    }
                }
                break;
        }
        $return_val['total'] = $aggregate;
        return $return_val;
    }

}
