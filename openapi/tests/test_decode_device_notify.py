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
        self.states = []
        self.response = response

    def addSmsTrade(self, data):
        self.trades.append(data)
        return self.response

    def setPending(self, data):
        self.states.append(data)
        return {'code': 1}


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
        decoder.process_notification(self.notification('OTP: 123456', 'com.google.android.apps.messaging'), api)
        self.assertEqual(api.trades, [])
        self.assertEqual(api.states, [{'id': 7, 'status': 2}])

    def test_failed_upload_remains_pending_for_retry(self):
        for response in [None, {'code': 0, 'msg': 'temporary failure'}]:
            api = FakeApi(response)
            decoder.process_notification(self.notification(), api)
            self.assertEqual(len(api.trades), 1)
            self.assertEqual(api.states, [])


if __name__ == '__main__':
    unittest.main()
