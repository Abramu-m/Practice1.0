<template>
  <div class="vital-signs-form">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="fas fa-heartbeat mr-2"></i>
          Record Vital Signs
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="submitVitalSigns">
          <div class="row">
            <!-- Basic Vitals -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Basic Vital Signs</h6>
              
              <div class="form-group">
                <label for="pulse_rate">Pulse Rate (bpm)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="pulse_rate"
                  v-model.number="form.pulse_rate"
                  min="30" 
                  max="200"
                  placeholder="e.g., 72"
                >
              </div>

              <div class="form-group">
                <label for="temperature">Temperature (°C)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="temperature"
                  v-model.number="form.temperature"
                  min="30" 
                  max="45"
                  step="0.1"
                  placeholder="e.g., 36.5"
                >
              </div>

              <div class="form-group">
                <label for="respiratory_rate">Respiratory Rate (per min)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="respiratory_rate"
                  v-model.number="form.respiratory_rate"
                  min="10" 
                  max="60"
                  placeholder="e.g., 18"
                >
              </div>

              <div class="form-group">
                <label for="oxygen_saturation">Oxygen Saturation (%)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="oxygen_saturation"
                  v-model.number="form.oxygen_saturation"
                  min="70" 
                  max="100"
                  placeholder="e.g., 98"
                >
              </div>
            </div>

            <!-- Measurements -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">Physical Measurements</h6>
              
              <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="weight"
                  v-model.number="form.weight"
                  min="1" 
                  max="300"
                  step="0.1"
                  placeholder="e.g., 70.5"
                >
              </div>

              <div class="form-group">
                <label for="height">Height (cm)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="height"
                  v-model.number="form.height"
                  min="30" 
                  max="250"
                  placeholder="e.g., 175"
                >
              </div>

              <!-- BMI Display -->
              <div v-if="calculatedBMI" class="alert alert-info">
                <strong>BMI: {{ calculatedBMI.value }}</strong> - {{ calculatedBMI.category }}
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="systolic_pressure">Systolic BP (mmHg)</label>
                    <input 
                      type="number" 
                      class="form-control" 
                      id="systolic_pressure"
                      v-model.number="form.systolic_pressure"
                      min="60" 
                      max="250"
                      placeholder="e.g., 120"
                    >
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="diastolic_pressure">Diastolic BP (mmHg)</label>
                    <input 
                      type="number" 
                      class="form-control" 
                      id="diastolic_pressure"
                      v-model.number="form.diastolic_pressure"
                      min="30" 
                      max="150"
                      placeholder="e.g., 80"
                    >
                  </div>
                </div>
              </div>

              <!-- Blood Pressure Category -->
              <div v-if="bloodPressureCategory" class="alert" :class="bloodPressureCategoryClass">
                <strong>Blood Pressure:</strong> {{ bloodPressureCategory }}
              </div>
            </div>
          </div>

          <!-- Additional Measurements -->
          <div class="row mt-3">
            <div class="col-md-12">
              <h6 class="text-primary mb-3">Additional Measurements (Pediatric)</h6>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="muac">MUAC (Mid-Upper Arm Circumference)</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="muac"
                  v-model="form.muac"
                  placeholder="e.g., 15.5 cm"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="ofc">OFC (Occipital-Frontal Circumference)</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="ofc"
                  v-model="form.ofc"
                  placeholder="e.g., 35 cm"
                >
              </div>
            </div>
          </div>

          <!-- Error Display -->
          <div v-if="error" class="alert alert-danger">
            {{ error }}
          </div>

          <!-- Success Display -->
          <div v-if="success" class="alert alert-success">
            {{ success }}
          </div>

          <!-- Submit Button -->
          <div class="form-group mt-4">
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="loading"
            >
              <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ loading ? 'Recording...' : 'Record Vital Signs' }}
            </button>
            <button 
              type="button" 
              class="btn btn-secondary ml-2"
              @click="clearForm"
            >
              <i class="fas fa-times mr-2"></i>
              Clear
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'VitalSignsForm',
  props: {
    consultationId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      form: {
        pulse_rate: null,
        temperature: null,
        respiratory_rate: null,
        weight: null,
        height: null,
        systolic_pressure: null,
        diastolic_pressure: null,
        oxygen_saturation: null,
        muac: '',
        ofc: ''
      },
      loading: false,
      error: null,
      success: null
    };
  },
  computed: {
    calculatedBMI() {
      if (this.form.weight && this.form.height) {
        const heightInMeters = this.form.height / 100;
        const bmi = (this.form.weight / (heightInMeters * heightInMeters)).toFixed(1);
        
        let category = '';
        if (bmi < 18.5) category = 'Underweight';
        else if (bmi < 25) category = 'Normal weight';
        else if (bmi < 30) category = 'Overweight';
        else category = 'Obese';
        
        return { value: bmi, category };
      }
      return null;
    },
    
    bloodPressureCategory() {
      if (this.form.systolic_pressure && this.form.diastolic_pressure) {
        const systolic = this.form.systolic_pressure;
        const diastolic = this.form.diastolic_pressure;
        
        if (systolic < 120 && diastolic < 80) return 'Normal';
        if (systolic < 130 && diastolic < 80) return 'Elevated';
        if (systolic < 140 || diastolic < 90) return 'High Blood Pressure Stage 1';
        if (systolic < 180 || diastolic < 120) return 'High Blood Pressure Stage 2';
        return 'Hypertensive Crisis';
      }
      return null;
    },
    
    bloodPressureCategoryClass() {
      if (!this.bloodPressureCategory) return '';
      
      switch (this.bloodPressureCategory) {
        case 'Normal': return 'alert-success';
        case 'Elevated': return 'alert-warning';
        case 'High Blood Pressure Stage 1': return 'alert-warning';
        case 'High Blood Pressure Stage 2': return 'alert-danger';
        case 'Hypertensive Crisis': return 'alert-danger';
        default: return 'alert-info';
      }
    }
  },
  methods: {
    async submitVitalSigns() {
      this.loading = true;
      this.error = null;
      this.success = null;
      
      try {
        const response = await axios.post(
          `/api/clinical/consultations/${this.consultationId}/vital-signs`,
          this.form
        );
        
        this.success = response.data.message;
        this.$emit('vitals-recorded', response.data.vitals);
        
        // Show calculated values
        if (response.data.calculated_values) {
          const calc = response.data.calculated_values;
          this.success += ` (BMI: ${calc.bmi}, Category: ${calc.bmi_category})`;
        }
        
        // Clear form after successful submission
        setTimeout(() => {
          this.clearForm();
        }, 2000);
        
      } catch (err) {
        if (err.response?.data?.errors) {
          // Laravel validation errors
          const errors = err.response.data.errors;
          this.error = Object.values(errors).flat().join(', ');
        } else {
          this.error = err.response?.data?.message || 'Failed to record vital signs';
        }
      } finally {
        this.loading = false;
      }
    },
    
    clearForm() {
      this.form = {
        pulse_rate: null,
        temperature: null,
        respiratory_rate: null,
        weight: null,
        height: null,
        systolic_pressure: null,
        diastolic_pressure: null,
        oxygen_saturation: null,
        muac: '',
        ofc: ''
      };
      this.error = null;
      this.success = null;
    }
  }
};
</script>

<style scoped>
.vital-signs-form .form-group label {
  font-weight: 500;
  color: #495057;
}

.vital-signs-form .alert {
  border-radius: 6px;
  margin-bottom: 1rem;
}

.vital-signs-form .btn {
  border-radius: 6px;
}

.vital-signs-form .card {
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.vital-signs-form .text-primary {
  border-bottom: 2px solid #007bff;
  padding-bottom: 5px;
}
</style>
