#!/bin/bash

# Setup Virtual Environment
python3 -m venv venv
source venv/bin/activate

# Install dependencies
pip install --force-reinstall -r requirements.txt

# Run Benchmark
python benchmark.py
