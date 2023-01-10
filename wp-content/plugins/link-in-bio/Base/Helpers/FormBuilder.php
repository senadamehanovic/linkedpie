<?php

namespace linkinbio\Base\Helpers;

use linkinbio\Base\Helpers\FormBuilder\FormInput;
use const linkinbio\Base\Helpers\FormBuilder\NORMAL_FORM;
use const linkinbio\Base\Helpers\FormBuilder\TYPE_CHECKBOX;
use const linkinbio\Base\Helpers\FormBuilder\TYPE_HEADER;
use const linkinbio\Base\Helpers\FormBuilder\INLINE_FORM;
use const linkinbio\Base\Helpers\FormBuilder\TYPE_SELECT;
use const linkinbio\Base\Helpers\FormBuilder\TYPE_SUBMIT;
use const linkinbio\Base\Helpers\FormBuilder\TYPE_TEXT;
use const linkinbio\Base\Helpers\FormBuilder\TYPE_TEXTAREA;
class FormBuilder
{
    protected string $type;
    /**
     * @param string $type either TYPE_INLINE or TYPE_NORMAL
     */
    public function __construct(string $type = null)
    {
        $this->type = $type ?? NORMAL_FORM;
    }
    protected function checkFormMethod(&$string)
    {
        foreach (['get', 'post'] as $m) {
            if (str_starts_with($m . ":", $string)) {
                $method = $m;
                $string = substr(strlen($method) + 1, $string);
                return $m;
            }
        }
        return "post";
    }
    /**
     * @param string|FormInput|string[] $form_data the data to build the form from
     * either a string or an array containing FormInput or build string per input
     */
    public function create($form_data, $method = null, $action = null, $rowpostfix = null, $rowprefix = null)
    {
        if (is_string($form_data)) {
            $method = $method ?? $this->checkFormMethod($form_data);
            $form_data = $this->parseInputs($form_data);
        } else {
            if (is_array($form_data)) {
                if (is_string($form_data[0])) {
                    $method = $method ?? $this->checkFormMethod($form_data[0]);
                }
                for ($i = 0; $i < sizeof($form_data); $i++) {
                    if (is_string($form_data[$i])) {
                        $form_data[$i] = $this->parseInput($form_data[$i]);
                    }
                }
            }
        }
        $this->createForm($form_data, $method, $action, $rowpostfix, $rowprefix);
    }
    /**
     * converts string of format name=default|type{att1,att2}[opt1,opt2] to FormInput object
     * minimum is name|type, others aer optional
     * @param string $input_string
     * @return FormInput
     */
    public function parseInput(string $input_string) : FormInput
    {
        $input_string .= "\n";
        // flush
        $args = [];
        $mode = "title";
        $current = "";
        $options_depth = 0;
        $mode_maps = ["|" => "type", "<" => "options", "=" => "default_value", "{" => "atts", "\n" => 'flush', '\'' => 'name'];
        for ($i = 0; $i < strlen($input_string); $i++) {
            $char = $input_string[$i];
            if ($char == ">" || $char == "}") {
                $options_depth--;
                continue;
            }
            if ($options_depth == 0 && isset($mode_maps[$char])) {
                if (in_array($char, ['<', '{'])) {
                    $options_depth++;
                }
                if (in_array($mode, ['options', 'atts'])) {
                    $current = array_reduce(explode(',', $current), function ($r, $i) {
                        $i = explode("=", $i);
                        $r[trim(sanitize_key($i[0]))] = trim($i[sizeof($i) - 1]);
                        return $r;
                    });
                } else {
                    $current = trim($current);
                }
                if (empty($args[$mode] ?? '')) {
                    $args[$mode] = $current;
                }
                $current = "";
                $mode = $mode_maps[$char];
                continue;
            }
            $current .= $char;
        }
        $args['name'] = $args['name'] ?? FormInput::name_from_title($args['title']);
        return new FormInput($args["title"], $args["type"] ?? TYPE_TEXT, $_POST[$args["name"]] ?? $args['default_value'] ?? null, $args["atts"] ?? [], $args['options'] ?? [], $args['name'] ?? null);
    }
    /**
     * @param string $form_string
     * @return FormInput[]
     */
    public function parseInputs(string $form_string)
    {
        $current = "";
        $option_depth = 0;
        $inputs = [];
        for ($i = 0; $i < strlen($form_string); $i++) {
            switch ($form_string[$i]) {
                case ',':
                    if ($option_depth == 0) {
                        $inputs[] = $this->parseInput($current);
                        $current = "";
                        $option_depth = 0;
                        continue 2;
                    }
                    break;
                case '<':
                case '{':
                    $option_depth++;
                    break;
                case '>':
                case '}':
                    $option_depth--;
                default:
            }
            $current .= $form_string[$i];
        }
        if (!empty($current)) {
            $inputs[] = $this->parseInput($current);
        }
        return $inputs;
    }
    /**
     * @param FormInput[] $inputs
     */
    protected function createForm($inputs, $method = "post", $action = null, $rowprefix = null, $rowpostfix = null)
    {
        $method = $method ?? "post";
        $rowprefix = $rowprefix ?? ($this->type == INLINE_FORM ? "" : "<div class=\"form-row\">");
        $rowpostfix = $rowpostfix ?? ($this->type == INLINE_FORM ? "" : "</div>");
        $action = $action ?? htmlspecialchars($_SERVER['REQUEST_URI']);
        if (!IterableUtil::in_iterable($inputs, fn($i) => $i->type == TYPE_SUBMIT)) {
            $inputs[] = new FormInput("Submit", TYPE_SUBMIT, "submit");
        }
        echo "<form action=\"{$action}\" method=\"{$method}\" class=\"eyeseet-form\" enctype=\"multipart/form-data\">";
        foreach ($inputs as $input) {
            $this->printInput($input, $rowprefix, $rowpostfix);
        }
        echo "</form>";
    }
    public function getInput($i, $prefix = "", $postfix = "")
    {
        ob_start();
        $this->printInput($i, $prefix, $postfix);
        return ob_get_clean();
    }
    /**
     * @param string|FormInput $i
     */
    public function printInput($i, $prefix = "", $postfix = "")
    {
        if (is_string($i)) {
            $i = $this->parseInput($i);
        }
        $print_pre_post = $i->type !== TYPE_SUBMIT;
        if ($print_pre_post) {
            echo $prefix;
        }
        $label = "";
        if ($i->print_label) {
            $label = "<label for=\"{$i->id}\">{$i->title}</label>";
        }
        $ids = "id=\"{$i->id}\" name=\"{$i->name}\"";
        $atts = IterableUtil::join($i->atts, fn($v, $k) => "{$k}=\"{$v}\"");
        $input_base = "<input {$ids} type=\"{$i->type}\" {$atts} %s>";
        switch ($i->type) {
            case TYPE_HEADER:
            case "header":
            case "head":
                if ($this->type == INLINE_FORM) {
                    echo "<b>{$i->title}</b>";
                } else {
                    echo "<h3>{$i->title}</h3>";
                }
                break;
            case TYPE_TEXTAREA:
                if ($this->type !== INLINE_FORM) {
                    echo $label;
                }
                echo "<textarea {$ids}>{$i->value}</textarea>";
                break;
            case TYPE_CHECKBOX:
                $print_label_after = $i->options['print_label_after'] ?? $this->type == INLINE_FORM;
                if (!$print_label_after) {
                    echo $label;
                }
                printf($input_base, $i->value ? "checked" : "");
                if ($print_label_after) {
                    echo $label;
                }
                break;
            case TYPE_SELECT:
                if ($this->type == INLINE_FORM) {
                    $i->options = ["" => sprintf(__("Select %s", 'linkinbio'), $i->title)] + $i->options;
                } else {
                    echo $label;
                }
                $options = IterableUtil::join($i->options, fn($v, $k) => "<option value=\"{$k}\" " . ($k == $i->value ? "selected" : "") . ">{$v}</option>");
                echo "<select {$ids} {$atts}>{$options}</select>";
                break;
            case TYPE_SUBMIT:
                echo "<button {$ids} type=\"submit\" value=\"{$i->value}\">{$i->title}</button>";
                break;
            default:
                echo $label;
                printf($input_base, "value=\"{$i->value}\"");
                break;
        }
        if ($print_pre_post) {
            echo $postfix;
        }
    }
}
namespace linkinbio\Base\Helpers\FormBuilder;

const INLINE_FORM = "inline";
const NORMAL_FORM = "normal";
const TYPE_CHECKBOX = "checkbox";
const TYPE_HIDDEN = "hidden";
const TYPE_TEXT = "text";
const TYPE_EMAIL = "email";
const TYPE_TEXTAREA = "textarea";
const TYPE_SUBMIT = "submit";
const TYPE_FILE = "file";
const TYPE_HEADER = "h3";
const TYPE_SELECT = "select";
class FormInput
{
    public $id, $name, $title, $type, $default_value, $value, $atts, $options, $print_label;
    function __construct($title, $type, $default_value = null, $atts = [], $options = [], $name = null)
    {
        $this->title = __($title, 'linkinbio');
        $this->id = sanitize_key($title);
        $this->name = $name ?? static::name_from_title($this->title);
        $this->print_label = !in_array($type, [TYPE_SUBMIT, TYPE_HEADER, TYPE_HIDDEN]);
        $this->type = $type;
        $this->default_value = $default_value;
        $this->atts = $atts;
        $this->options = $options;
        $this->set_value_from_post();
    }
    protected function set_value_from_post()
    {
        $matches = [];
        if (preg_match("/(.*)\\[(.*)\\]/ui", $this->name, $matches)) {
            $submitval = $_POST[$matches[1]][$matches[2]] ?? $_GET[$matches[1]][$matches[2]] ?? null;
        } else {
            $submitval = $_POST[$this->name] ?? $_GET[$this->name] ?? null;
        }
        $this->value = $submitval !== null ? sanitize_text_field(stripslashes($submitval)) : $this->default_value;
    }
    public function set_prop($prop, $val) : FormInput
    {
        $this->{$prop} = $val;
        if ($prop == 'name') {
            $this->set_value_from_post();
        }
        return $this;
    }
    public function add_opt($opt, $val) : FormInput
    {
        $this->options[$opt] = $val;
        return $this;
    }
    public static function name_from_title($title)
    {
        return preg_replace('/[^a-z0-9_\\-\\[\\]]/', '', strtolower($title));
    }
}