# BankMellat
library Bank Mellat IRAN

# Load library
<pre>
$this->load->library('Mellat');
</pre>

# Send parameter to library and Send to Bank
<pre>
$this->Mellat->set_options($terminal,$username,$password,$amount,$order,$callback);
$this->Mellat->send();
</pre>

# Callback
<pre>
$this->Mellat->set_options($terminal,$username,$password);
$this->Mellat->get($_POST);
</pre>
