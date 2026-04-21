#!/usr/bin/env python3
##text analysis script##
import sys
import os
import warnings
from transformers.utils import logging
from gliner import GLiNER

# Disable symlinks on Windows - causes permissions issues if not!!
os.environ["HF_HUB_DISABLE_SYMLINKS"] = "1"

# Squash output
logging.disable_progress_bar()
warnings.filterwarnings("ignore")

PPI_TEMPLATE = {
  "FIRST_NAME": 0,
  "SURNAME": 0,
  "DATE_OF_BIRTH": 0,
  "MOBILE_NUMBER": 0,
  "NATIONAL_INSURANCE_NUMBER": 0,
  "SCHOOL_NAME": 0,
  "BIRTHPLACE": 0,
  "ADDRESS": 0,
  "BANK_ACCOUNT_NUMBER": 0,
  "SORT_CODE": 0,
  "BACS": 0,
  "IBAN": 0,
  "DEBIT_CARD_NUMBER": 0,
  "CARD_CVC": 0,
  "CARD_EXPIRY": 0
}

labels = list(PPI_TEMPLATE.keys())


def NER_analysis(post_history: str, model) -> str:
  """ Runs NER analysis on historic user posts
  This function runs NER analysis on historic user posts. It will then add a count
  to the PII count object defined above, which is then parsed to create a human-readable
string i.e. "Cvv, Iban, and Sort Code"
  """

  #Use a copy of the counts to avoid errors
  PPI_counts = PPI_TEMPLATE.copy()
  flags = []

  entities = model.predict_entities(post_history, labels)

  # count detected entities
  for ent in entities:
    label = ent.get("label")
    if label in PPI_counts:
        PPI_counts[label] += 1

  # create output list
  for key, value in PPI_counts.items():
    if value > 0:
      flags.append(key.replace("_", " ").upper())
  
  # format output string
  flags_length = len(flags)
  if flags_length == 0:
    return "None"
  elif flags_length == 1:
    return flags[0]
  else:
    return (", ".join(flags[:-1]) + f", and {flags[-1]}")

if __name__ == "__main__":
  # post_history is a string passed in which contains a single historic post
  post_history = sys.argv[1]

  # get pretrained model from hugging face which is compatible with gLiNER
  model = GLiNER.from_pretrained("nvidia/gliner-PII")

  #run analysis
  results = NER_analysis(post_history, model)
  print(results)
