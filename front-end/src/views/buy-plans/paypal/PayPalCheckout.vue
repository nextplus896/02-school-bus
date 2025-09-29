<template>
  <div class="paypal-container">
    <div v-if="!paymentCompleted">
      <h2>Complete Your Payment</h2>
      <div class="order-summary">
        <h3>Order Summary</h3>
        <p>Total: ${{ amount }}</p>
      </div>

      <!-- PayPal buttons will render here -->
      <div ref="paypalButtonContainer"></div>

      <div v-if="loading" class="loading">Processing payment...</div>
      <div v-if="error" class="error">{{ error }}</div>
    </div>

    <div v-else class="success">
      <h2>Payment Successful!</h2>
      <p>Your order ID: {{ orderId }}</p>
      <p>Thank you for your purchase.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PayPalCheckout',
  props: {
    amount: {
      type: Number,
      required: true
    },
    currency: {
      type: String,
      default: 'USD'
    },
    apiUrl: {
      type: String,
      required: true
    },
    PAYPAL_CLIENT_ID: {
      type: String,
      required: true,
      default: 'AX5MkPYORaGM_izgFfRiCbUnWpIEdPDQMTcmtcvDLkc1F7lc_CvKtV_3NzRtqJIBB7hgpQtRvUzVY74U'
    }
  },
  data() {
    return {
      loading: false,
      error: null,
      orderId: null,
      paymentCompleted: false
    }
  },
  mounted() {
    this.loadPayPalScript();
  },
  methods: {
    loadPayPalScript() {
      // Load the PayPal JS SDK
      const script = document.createElement('script');
      script.src = `https://www.paypal.com/sdk/js?client-id=${this.PAYPAL_CLIENT_ID}&currency=${this.currency}`;
      script.onload = this.initPayPal;
      document.body.appendChild(script);
    },

    initPayPal() {
      if (window.paypal) {
        window.paypal.Buttons({
          // Set up the transaction
          createOrder: (data, actions) => {
            this.loading = true;

            // Create order on client side
            return actions.order.create({
              purchase_units: [{
                amount: {
                  currency_code: this.currency,
                  value: this.amount.toString()
                }
              }]
            }).then(orderId => {
              this.orderId = orderId;
              this.loading = false;
              return orderId;
            }).catch(err => {
              this.loading = false;
              this.error = "Failed to create order. Please try again.";
              console.error(err);
            });
          },

          // Called when payment is approved by the user
          onApprove: (data, actions) => {
            this.loading = true;

            // Send the order ID to your server for capture
            return fetch(`${this.apiUrl}/capture-paypal-order`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                orderId: data.orderID
              })
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Payment capture failed');
              }
              return response.json();
            })
            .then(captureData => {
              const captureStatus = captureData.status || captureData.captureStatus;

              if (captureStatus === 'COMPLETED') {
                this.paymentCompleted = true;
                this.$emit('payment-success', {
                  orderId: data.orderID,
                  captureData: captureData
                });
              } else {
                throw new Error(`Payment not completed. Status: ${captureStatus}`);
              }
              this.loading = false;
            })
            .catch(err => {
              this.loading = false;
              this.error = "Payment processing failed. Please try again.";
              console.error(err);
              this.$emit('payment-error', err);
            });
          },

          onError: (err) => {
            this.loading = false;
            this.error = "An error occurred. Please try again.";
            console.error(err);
            this.$emit('payment-error', err);
          }
        }).render(this.$refs.paypalButtonContainer);
      }
    }
  }
}
</script>

<style scoped>
.paypal-container {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
}

.order-summary {
  margin-bottom: 20px;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 5px;
}

.loading, .error, .success {
  margin-top: 20px;
  padding: 12px;
  border-radius: 4px;
  text-align: center;
}

.loading {
  background-color: #f5f5f5;
}

.error {
  background-color: #ffebee;
  color: #d32f2f;
}

.success {
  background-color: #e8f5e9;
  color: #2e7d32;
  padding: 20px;
}
</style>
