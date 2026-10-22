import importlib.util
import json
from pathlib import Path
import unittest


spec = importlib.util.spec_from_file_location(
    'decode_device_notify', Path(__file__).resolve().parents[1] / 'decodeDeviceNotify.py'
)
decoder = importlib.util.module_from_spec(spec)
spec.loader.exec_module(decoder)

TEXT = 'Tài khoản: 800xx6204 Số dư: 70,300 VND ND: 80000186204-6282IBT1i1WJWDGM-DCZ8P1HF'


class FakeApi:
    def __init__(self, response=None):
        self.trades = []
        self.codes = []
        self.states = []
        self.response = response

    def addSmsTrade(self, data):
        self.trades.append(data)
        return self.response

    def setPending(self, data):
        self.states.append(data)
        return {'code': 1}

    def addSmsCode(self, data):
        self.codes.append(data)
        return self.response


class DecodeDeviceNotifyTests(unittest.TestCase):
    def notification(self, text=TEXT, package='com.msb.digibank.retail'):
        return {'id': 7, 'content': json.dumps({'package_name': package, 'extrasText': text})}

    def test_uploads_exactly_four_fields(self):
        api = FakeApi({'code': 1})
        decoder.process_notification(self.notification(), api)
        self.assertEqual(api.trades, [{
            'notify_id': 7, 'bank_number': '80000186204', 'balance': 70300, 'remark': 'DCZ8P1HF'
        }])
        self.assertEqual(api.states, [])

    def test_zero_balance_and_empty_remark_are_valid(self):
        data = decoder.decode_digibank(7, 'Số dư: 0 VND ND: 000123456789')
        self.assertEqual(data['balance'], 0)
        self.assertEqual(data['bank_number'], '000123456789')
        self.assertEqual(data['remark'], '')

    def test_prefers_balance_over_an_earlier_transaction_amount(self):
        data = decoder.decode_digibank(7, 'Giao dich: 300 VND ' + TEXT)
        self.assertEqual(data['balance'], 70300)

    def test_invalid_transaction_is_failed_without_uploading(self):
        for text in ['', 'Số dư: 10 VND', 'ND: 80000186204', TEXT.replace('70,300', '-70,300'),
                     TEXT.replace('70,300', '2,147,483,648'),
                     TEXT.replace('80000186204', '1' * 21)]:
            with self.subTest(text=text):
                api = FakeApi()
                decoder.process_notification(self.notification(text), api)
                self.assertEqual(api.trades, [])
                self.assertEqual(api.states, [{'id': 7, 'status': -1}])

    def test_invalid_json_does_not_continue_as_a_transaction(self):
        for content in ['bad JSON', '[]', 'null', '123']:
            api = FakeApi()
            decoder.process_notification({'id': 7, 'content': content}, api)
            self.assertEqual(api.trades, [])
            self.assertEqual(api.states, [{'id': 7, 'status': -1}])

    def test_other_notifications_only_report_processing_status(self):
        api = FakeApi()
        decoder.process_notification(self.notification('ordinary notification', 'com.example.other'), api)
        self.assertEqual(api.trades, [])
        self.assertEqual(api.states, [{'id': 7, 'status': 2}])

    def test_extracts_keyword_codes_and_preserves_leading_zero(self):
        for text, expected in [
            ('Ma OTP xac thuc la 535928 hieu luc 1 phut.', '535928'),
            ('Mã xác thực: 012345', '012345'),
            ('Mã xác nhận của bạn là 1234', '1234'),
            ('Your verification code is 009876', '009876'),
            ('123456 is your OTP', '123456'),
            ('验证码为 0123，请勿泄露', '0123'),
        ]:
            with self.subTest(text=text):
                self.assertEqual(decoder.extract_otp(text), expected)

    def test_uploads_code_with_only_source_id_and_code(self):
        api = FakeApi({'code': 1})
        decoder.process_notification(self.notification('OTP: 001234', 'com.google.android.apps.messaging'), api)
        self.assertEqual(api.codes, [{'notify_id': 7, 'verify_code': '001234'}])
        self.assertEqual(api.states, [])
        self.assertEqual(api.trades, [])

    def test_code_can_be_found_in_ticker_when_extras_has_no_code(self):
        api = FakeApi({'code': 1})
        item = self.notification('New message', 'com.google.android.apps.messaging')
        content = json.loads(item['content'])
        content['tickerText'] = 'Mã OTP: 000123'
        item['content'] = json.dumps(content)
        decoder.process_notification(item, api)
        self.assertEqual(api.codes[0]['verify_code'], '000123')

    def test_missing_otp_marks_push_failed_without_upload(self):
        for text in ['Call 0912345678', 'Balance 70300 VND', 'OTP: 123456789', 'OTP expires in 1 minute']:
            api = FakeApi()
            decoder.process_notification(self.notification(text, 'com.google.android.apps.messaging'), api)
            self.assertEqual(api.codes, [])
            self.assertEqual(api.states, [{'id': 7, 'status': -1}])

    def test_failed_code_upload_keeps_source_pending(self):
        for response in [None, {'code': 0}]:
            api = FakeApi(response)
            decoder.process_notification(self.notification('OTP: 123456', 'com.google.android.apps.messaging'), api)
            self.assertEqual(len(api.codes), 1)
            self.assertEqual(api.states, [])

    def test_failed_upload_remains_pending_for_retry(self):
        for response in [None, {'code': 0, 'msg': 'temporary failure'}]:
            api = FakeApi(response)
            decoder.process_notification(self.notification(), api)
            self.assertEqual(len(api.trades), 1)
            self.assertEqual(api.states, [])


if __name__ == '__main__':
    unittest.main()
