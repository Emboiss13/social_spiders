from text_analysis import NER_analysis
from gliner import GLiNER

model = GLiNER.from_pretrained("nvidia/gliner-PII")

dataset = [
  {"text": "My name is John Smith", "expected": ["FIRST_NAME", "SURNAME"]},
  {"text": "Call me on 07700900123", "expected": ["MOBILE_NUMBER"]},
  {"text": "I was born on 12/05/1998", "expected": ["DATE_OF_BIRTH"]},
  {"text": "My IBAN is GB33BUKB20201555555555", "expected": ["IBAN"]},
  {"text": "Sort code: 20-15-55", "expected": ["SORT_CODE"]},
  {"text": "Account number: 12345678", "expected": ["BANK_ACCOUNT_NUMBER"]},
  {"text": "My card is 1234 5678 9012 3456 exp 12/26 CVV 123", "expected": ["DEBIT_CARD_NUMBER", "CARD_EXPIRY", "CARD_CVC"]},
  {"text": "I live at 10 Downing Street, London", "expected": ["ADDRESS"]},
  {"text": "I went to Oxford University", "expected": ["SCHOOL_NAME"]},
  {"text": "I was born in Manchester", "expected": ["BIRTHPLACE"]},
  {"text": "Hey guys, it's John here!", "expected": ["FIRST_NAME"]},
  {"text": "Finally moved to 221B Baker Street 🎉", "expected": ["ADDRESS"]},
  {"text": "New phone who dis? 07700900123", "expected": ["MOBILE_NUMBER"]},
  {"text": "Uni days at Cambridge were the best", "expected": ["SCHOOL_NAME"]},
  {"text": "Born in London but raised elsewhere", "expected": ["BIRTHPLACE"]},
  {"text": "Send it to my IBAN GB82WEST12345698765432 pls", "expected": ["IBAN"]},
  {"text": "DOB: 01-01-2000", "expected": ["DATE_OF_BIRTH"]},
  {"text": "Sort code is 30-00-00 if needed", "expected": ["SORT_CODE"]},
  {"text": "Me llamo Juan Pérez", "expected": ["FIRST_NAME", "SURNAME"]},
  {"text": "Mi número es 07700900123", "expected": ["MOBILE_NUMBER"]},
  {"text": "Nací el 5 de mayo de 1998", "expected": ["DATE_OF_BIRTH"]},
  {"text": "Mi IBAN es ES9121000418450200051332", "expected": ["IBAN"]},
  {"text": "Vivo en Calle Mayor 10, Madrid", "expected": ["ADDRESS"]},
  {"text": "Estudié en la Universidad de Barcelona", "expected": ["SCHOOL_NAME"]},
  {"text": "Nací en Sevilla", "expected": ["BIRTHPLACE"]},
  {"text": "Hola soy Ana!", "expected": ["FIRST_NAME"]},
  {"text": "Mi cumple es 12/08/1995 🎂", "expected": ["DATE_OF_BIRTH"]},
  {"text": "Nuevo piso en Calle Sol 15 😍", "expected": ["ADDRESS"]},
  {"text": "Mándalo a ES7620770024003102575766", "expected": ["IBAN"]},
  {"text": "Número nuevo: 07700900123", "expected": ["MOBILE_NUMBER"]},
  {"text": "Call me maybe 😉", "expected": []},
  {"text": "Number one fan here!", "expected": []},
  {"text": "My name is not important", "expected": []},
  {"text": "Born to be wild", "expected": []},
  {"text": "John was here", "expected": ["FIRST_NAME"]},
  {"text": "The code is 1234 but not a card", "expected": []},
  {"text": "This looks like an IBAN but isn't GB00TEST", "expected": []},
  {"text": "My number is one two three four", "expected": []}
]

def evaluate(dataset):

    correct = tp = fp = fn = total = 0

    for item in dataset:

        result = NER_analysis(item["text"], model)

        if result == "None":
            detected = set()
        else:
            detected = set(result.replace(", and ", ", ").split(", "))

        expected = set(label.replace("_", " ") for label in item["expected"])

        if detected == expected:
            correct += 1
        
        tp += len(detected & expected)
        fp += len(detected - expected)
        fn += len(expected - detected)
        
        total += 1

    # Accuracy = Number of correct predictions / Total number of predictions
    accuracy = correct / total

    # Precision = True positive / All positive results
    precision = tp / (tp + fp) if (tp + fp) else 0

    # Recall = True positive / True positive and False Negatives
    recall = tp / (tp + fn) if (tp + fn) else 0

    # F1 score = Combination of precision and recall metrics
    f1_score = 2*((precision*recall)/(precision+recall))

    print(f"Accuracy: {accuracy:.2f}")
    print(f"Precision: {precision:.2f}")
    print(f"Recall: {recall:.2f}")
    print(f"F1 Score: {f1_score:.2f}")


evaluate(dataset)