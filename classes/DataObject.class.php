<?php @session_start();

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/05/2017
 * Time: 20:43
 */

class DataObject
{
    private $data               = Array();
    private $error;
    private $db;
    private $table;
    private $rowcount;
    private $update                = Array();
    private $compte_principal;
    private $sql;
    private $liaison            = Array();
    private $tab_obj;
    private $preloadinner       = Array();
    private $tab_where          = Array();
    private $i_where            = 0;
    private $orderBy            = Array();
    private $limit              = 0;

    public function __construct($table)
    {
        global $db;
        $this->db                   = $db;
        $this->table                = $this->db->SQLFix($table);
    }

    public function addWhere($field, $value)
    {
        $this->tab_where[$this->i_where][$field] = $value;
        $this->i_where++;
    }

    public function orderBy($field, $direction='ASC')
    {
        $this->orderBy['field'] = $field;

        if(strtoupper($direction) != 'ASC' AND strtoupper($direction) != 'DESC')
            $direction = 'ASC';
        else
            $direction = strtoupper($direction);

        $this->orderBy['direction'] = $direction;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
    }

    private function generateRequest($begin)
    {
        $sql = $begin;
        $i=0;
        //echo count($this->tab_where);
        if($nb_where = count($this->tab_where))
        {
            foreach ($this->tab_where AS $where) {
                foreach ($where AS $k => $v) {
                    if (!$i)
                        $sql .= ' WHERE ';
                    if ($nb_where != $i AND $i > 0)
                        $sql .= ' AND ';
                    $sql .= ' ' . $this->db->SQLFix($k) . '="' . $this->db->SQLFix($v) . '"';
                }
                $i++;
            }
        }

        if(count($this->orderBy))
            $sql .= ' ORDER BY '.$this->db->SQLFix($this->orderBy['field']).' '.$this->orderBy['direction'];

        if($this->limit)
            $sql .= ' LIMIT '.$this->db->SQLFix($this->limit);

        $this->sql = $sql;
        //echo $sql.'<br />';
        return $sql;


    }

    public function findAll($compte_principal = true)
    {

        if($compte_principal)
            $this->addWhere('compte_principal', $_SESSION['compte_principal']);
        $sql = 'SELECT * FROM ' . $this->table;
        //echo($this->generateRequest($sql));

        $this->db->Query($this->generateRequest($sql));
        //echo $this->generateRequest($sql);
        $this->tab_obj = Array();
        $i = 0;
        if($this->db->RowCount()) {
            foreach ($this->db->RecordsArray() AS $entry) {
                //echo 'ok';
                $this->tab_obj[$i] = new DataObject($this->table);
                $this->tab_obj[$i]->setRowCount();
                foreach ($entry AS $k => $v)
                    if (!is_numeric($k))
                        $this->tab_obj[$i]->loadData($k, $v);
                $i++;
            }
        }
        $this->db->Close();
        return $this->tab_obj;
    }

    private function setRowCount()
    {
        $this->rowcount = 1;
    }

    private function loadData($k, $v)
    {
        $this->data[$k] = $v;
    }

    public function find($id, $field_id = 'id', $compte_principal = true)
    {
        $this->compte_principal = $compte_principal;
        $sql = 'SELECT * FROM ' . $this->table;

        $this->addWhere($field_id, $id);

        if ($this->compte_principal)
            $this->addWhere('compte_principal', $_SESSION['compte_principal']);

        $this->db->Query($this->generateRequest($sql));
        echo $this->db->Error();
        $this->rowcount = $this->db->RowCount();
        if ($this->rowcount > 1)
            die('Rowcount : <strong>' . $this->rowcount . '</strong> (more than 1) in [' . $sql . ']');
        if($this->db->RowCount())
            foreach ($this->db->RecordsArray()[0] AS $k => $v)
                if (!is_numeric($k))
                    $this->data[$k] = $v;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function __get($name)
    {
        if ($this->rowcount) {
            if (array_key_exists($name, $this->data))
                return $this->data[$name];
            else
                return 'Champs "' . $name . '" incorrect (get)';
        } else
            return '';
    }

    public function preLoadInner($column_name)
    {
        if (is_array($column_name))
            $tab_col = $column_name;
        else
            $tab_col[0] = $column_name;
        foreach ($tab_col AS $col)
            if (!in_array($col, $this->preloadinner)) {
                $sql = 'SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME ="' . $this->table . '" 
                            AND COLUMN_NAME ="' . $col . '" 
                            AND  REFERENCED_TABLE_NAME != "" 
                            AND REFERENCED_COLUMN_NAME != ""
                            ';
                $db = new MySQL();
                $db->Query($sql);
                $row = $db->Row();
                $sql = 'SELECT * FROM ' . $row->REFERENCED_TABLE_NAME;
                $db->Query($sql);
                foreach ($db->RecordsArray() AS $line)
                    foreach ($this->tab_obj As $obj)
                        if ($obj->$col == $line[$row->REFERENCED_COLUMN_NAME])
                            $obj->loadInnerValues($obj, $row->REFERENCED_TABLE_NAME, $col, $line);
                $this->preloadinner[] = $col;
            }
    }

    private function loadInnerValues($object, $table, $column, $line)
    {
        $object->createInnerObject($column, $table);
        foreach ($line AS $k => $v)
            if (!is_numeric($k))
                $object->loadInnerValue($column, $k, $v);
    }

    public function getliaison()
    {
        return $this->liaison;
    }

    private function createInnerObject($column, $table)
    {
        $this->liaison[$column] = new DataObject($table);
    }

    private function loadInnervalue($column, $k, $v)
    {
        $this->liaison[$column]->loadData($k, $v);
    }

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->data)) //champs existant
        {
            if (!is_object($this->liaison[$name])) {
                $sql = 'SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME ="' . $this->table . '" 
                        AND COLUMN_NAME ="' . $name . '" 
                        AND  REFERENCED_TABLE_NAME != "" 
                        AND REFERENCED_COLUMN_NAME != ""
                        ';
                $db = new MySQL();
                $db->Query($sql);
                $row = $db->Row();
                $rowcount = $db->RowCount();
                $this->liaison[$name] = new DataObject($row->REFERENCED_TABLE_NAME);
                $this->liaison[$name]->find($this->data[$name], $row->REFERENCED_COLUMN_NAME);
            } else
                $rowcount = 1;

            if ($rowcount) {
                return get_object_vars($this->liaison[$name])['data'][$arguments[0]];
            } else
                return $this->data[$name];
        } else
            return 'Champs "' . $name . '" incorrect (call)';
    }

    public function __set($name, $value)
    {
        if ($this->rowcount) {
            if (array_key_exists($name, $this->data)) {
                if ($name != 'id' AND $name != 'compte_principal') {
                    $this->update[$name] = $this->db->SQLFix($value);
                    $this->data[$name] = $this->update['$name'];
                }
            } else
                echo 'Champs "' . $name . '" incorrect (set)';
        }
    }

    public function update()
    {
        if (count($this->update)) {

            $sql = 'UPDATE ' . $this->table . ' SET ';
            $i = 1;
            foreach ($this->update AS $k => $v) {
                $sql .= $k . ' = "' . $v . '" ';
                if ($i < count($this->update))
                    $sql .= ', ';
                $i++;
            }
            $sql .= ' WHERE id="' . $this->data['id'] . '" ';
            if ($this->compte_principal)
                $sql .= 'AND compte_principal = "' . $_SESSION['compte_principal'] . '" ';
            $this->db->Query($sql);
        }
    }
}