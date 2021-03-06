<?php namespace Escapeboy\Epay;
class Epay
{
	private static $data = [];

    
    public static function generateInputFields(array $data, string $success_url = null, string $cancel_url=null) {
    	self::setData(array_change_key_case($data, CASE_LOWER));
    	return '
            <input type="hidden" name="PAGE" value="paylogin">
            <input type="hidden" name="ENCODED" value="'.self::encode().'">
            <input type="hidden" name="CHECKSUM" value="'.self::generateChecksum().'">
            <input type="hidden" name="URL_OK" value="'.($success_url ? $success_url : url(config('epay.success_url'))).'">
            <input type="hidden" name="URL_CANCEL" value="'.($cancel_url ? $cancel_url : url(config('epay.cancel_url'))).'">
        ';
    }

    public static function receiveNotification($requestInputs)
    {
        $encoded  = $requestInputs['encoded'];
        $checksum = $requestInputs['checksum'];
        $hmac   = self::generateChecksum($encoded);
        if ($hmac == $checksum) {
            $result = [];
            $result['data'] = base64_decode($encoded);
            $lines = explode("\n", $result['data']);
            $result['items'] = [];
            $response = '';
            foreach ($lines as $line) {
                if (preg_match("/^INVOICE=(\d+):STATUS=(PAID|DENIED|EXPIRED)(:PAY_TIME=(\d+):STAN=(\d+):BCODE=([0-9a-zA-Z]+))?$/", $line, $regs)) {
                    $item = [];
                    $item['invoice'] = $regs[1];
                    $item['status'] = $regs[2];
                    $item['pay_date'] = $regs[4];
                    $item['stan'] = $regs[5];
                    $item['bcode'] = $regs[6];
                    $result['items'][] = $item;
                    switch ($item['status']) {
                        case 'PAID':
                            $response .= "INVOICE=$regs[1]:STATUS=OK\n";
                            break;
                        case 'DENIED':
                            $response .= "INVOICE=$regs[1]:STATUS=ERR\n";
                            break;
                        default:
                            $response .= "INVOICE=$regs[1]:STATUS=NO\n";
                            break;
                    }
                }
            }
            $result['response'] = $response;
            return $result;
        }
        else {
            throw new Exception("Invalid checksum!", 1);
        }
    }

    public static function getSubmitUrl(){
    	return config('epay.submit_url');
    }

    private function setData(array $data){
    	$min = config('epay.client_id');
    	$invoice = ($data['invoice'] ? $data['invoice'] : sprintf("%.0f", rand() * 100000));
    	$amount = $data['amount'];
    	$exp_date = date('d.m.Y', strtotime(date('d.m.Y') . ' +'.config('epay.expire_days').' day'));
    	$descr = $data['descr'];
    	self::$data = '<<<DATA
			MIN='.$min.'
			INVOICE='.$invoice.'
			AMOUNT='.$amount.'
			EXP_TIME='.$exp_date.'
			DESCR='.$descr.',
			CURRENCY=BGN
			DATA';
    }

    private function generateChecksum(){
    	return hash_hmac('sha1', self::encode(), config('epay.secret'));
    }

    private function encode(){
    	return base64_encode(self::$data);
    }


}
