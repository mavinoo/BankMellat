# BankMellat
library Bank Mellat IRAN

# Load library
$this->load->library('Mellat');

# Send parameter to library and Send to Bank
$this->Mellat->set_options($terminal,$username,$password,$amount,$order,$callback);

$this->Mellat->send();

# Callback
$this->Mellat->set_options($terminal,$username,$password);

$this->Mellat->get($_POST);

