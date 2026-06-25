#!/bin/bash
#SBATCH --job-name=evaluate_scenarios
#SBATCH --output=evaluate_scenarios_%j.log
#SBATCH --error=evaluate_scenarios_%j.err
#SBATCH --nodes=1
#SBATCH --ntasks=1
#SBATCH --cpus-per-task=4
#SBATCH --mem=16G
#SBATCH --time=24:00:00

# Change to the directory from which the job was submitted
cd $SLURM_SUBMIT_DIR

# Setup Virtual Environment
python3 -m venv venv
source venv/bin/activate

# Install dependencies
pip install --force-reinstall -r requirements.txt

# Run Evaluation Scenarios
python evaluate_scenarios.py
