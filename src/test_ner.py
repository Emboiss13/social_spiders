import pytest

from text_analysis import NER_analysis, PPI_TEMPLATE

# Helper to reset counts before each test
@pytest.fixture(autouse=True)
def reset_counts():
    PPI_counts = PPI_TEMPLATE.copy()
    for key in PPI_counts:
        PPI_counts[key] = 0

# Test when there is no entity within the string
def test_no_entities():
    class MockModel:
        def predict_entities(self, text, labels):
            return []

    result = NER_analysis("Hello world", MockModel())
    assert result == "None"

# Test when there is one entity
def test_single_entity():
    class MockModel:
        def predict_entities(self, text, labels):
            return [{"label": "FIRST_NAME"}]

    result = NER_analysis("My name is John", MockModel())
    assert result == "FIRST NAME"

# Test when there is multiple entities
def test_multiple_entities():
    class MockModel:
        def predict_entities(self, text, labels):
            return [
                {"label": "FIRST_NAME"},
                {"label": "SURNAME"},
                {"label": "BACS"}
            ]

    result = NER_analysis("Test text", MockModel())
    assert result == "FIRST NAME, SURNAME, and BACS"

# duplicate
def test_multiple_with_duplicates():
    class MockModel:
        def predict_entities(self, text, labels):
            return [
                {"label": "FIRST_NAME"},
                {"label": "SURNAME"},
                {"label": "SURNAME"},
            ]

    result = NER_analysis("John Smith", MockModel())
    assert result == "FIRST NAME, and SURNAME"

def test_unknown_label():
    class MockModel:
        def predict_entities(self, text, labels):
            return [{"label": "UNKNOWN_LABEL"}]

    result = NER_analysis("Test", MockModel())
    assert result == "None"
