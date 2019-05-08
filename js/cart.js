Vue.component('cart',{
  props: ['product', 'i'],
  methods: {
    prettyPrice:function(data){
      return this.$parent.prettyPrice(data);
    },
    calcTotal: function(){
      return this.product.item.price * this.product.item.quantity;
    }
  },
  template: '#cart'
});

Vue.component('cart-actions',{
  props: ['coupon_code'],
  data: function(){
    return {
      mcoupon_code: this.coupon_code
    }
  },
  methods:{
    delete_coupon : function(code){
    }
  },
  template:'#cart-actions'
});

Vue.component('cart-totals',{
  props: ['totals', 'coupons', 'shipping_methods'],
  methods: {
    prettyPrice:function(data){
      return this.$parent.prettyPrice(data);
    },
    updateShippingMethod:function(data){
      //console.log(data);
      return this.$parent.updateShippingMethod(data);
      return;
    }
  },
  template:'#cart-totals'
});

Vue.component('coupons', {
  props:['discount', 'code'],
  methods: {prettyPrice:function(data){ return this.$parent.prettyPrice(data); }},
  template: '#coupons'
});

Vue.component('product-list',{
  props: ['product', 'i'],
  methods: {
    prettyPrice:function(data){
      return this.$parent.prettyPrice(data);
    },
    add_custom_product:function(id, name, price){
      console.log('Product List Component');
      return this.$parent.addCustomProduct(id, name, price);
    }
  },
  template: '#product-list'
});

Vue.component('custom-product',{
  props: ['id', 'productname', 'name', 'price'],
  methods: {
    add_custom_product:function(){
      console.log('Custom Product Component');
      //console.log("ID: "+this.id);
      //console.log("Name: "+this.name);
      //console.log("Price: "+this.price);
      return this.$parent.add_custom_product(this.id, this.name, this.price);
    }},
  template: '#custom-product'
});

Vue.component('text-filter',{
  props: ['filter'],
  data: function(){
    return {
      mfilter: this.filter
    }
  },
  methods:{
    clearSearch:function(){
      this.mfilter = '';
      this.$parent.filterProducts(this.mfilter);
      console.log("Clear");
    }
  },
  template: '#text-filter'
});

Vue.component('shipping-picker',{
  props: ['method', 'i'],
  template: '#shipping-picker'
});

let cartView = new Vue({
  el: '#cart_app',
  data: {
    product_id : '',
    cartdata : [],
    coupons : [],
    totals: [],
    textFilter: '',
    product_list: [],
    product_view: [],
    shipping_methods: [],
    disabler: '#contentBlocker'
  },
  created: function(){
    this.getProducts();
    this.refresh();
    this.getCoupons();
    this.getShippingMethods();
  },
  methods: {
    test: function(data='null'){
      console.log("Testing" + data);
    },
    disablePos: function(){
      jQuery('#contentBlocker').css('display', 'block');
      setTimeout(function(){jQuery('#contentBlocker').css('opacity', '0.3');}, 200);
    },
    enablePos: function(){
      jQuery('#contentBlocker').css('opacity', '0');
      setTimeout(function(){jQuery('#contentBlocker').css('display', 'none');}, 300);
      //jQuery('#contentBlocker').css('display', 'none');
    },
    prettyPrice: function(num){
      if (typeof num == 'undefined'){
        return '';
      }
      num = parseFloat(num);
      left = Math.floor(num).toLocaleString('en');

      right = num.toFixed(2);
      right = right.substring(right.indexOf('.'));
      return '$'+left+right;
    },
    refresh: function(){

      let vueInstance = this;

      jQuery.ajax({
        method:"GET",
        url:'https://igniteglass.com/pos/api/cart.php'
      }).done(function(data){
        vueInstance.cartdata = data;
        vueInstance.updateTotals();
      });
    },
    addProduct: function(data){
      this.disablePos();
      let vueInstance = this;

      jQuery.ajax({
        method:"POST",
        url:'https://igniteglass.com/pos/api/cart.php',
        data: {
          product_id:data,
          quantity:1
        }
      }).done(function(data){
        vueInstance.refresh();
      }).always(function(data, status, xhr){
        vueInstance.enablePos();
      });

    },
    addCustomProduct: function(id, name, price){
      this.disablePos();
      let vueInstance = this;

      console.log('App Level Custom Product');
      console.log('Id: ' + id);
      console.log('Name: ' + name);
      console.log('Price: ' + price);

      jQuery.ajax({
        method:"POST",
        url:'https://igniteglass.com/pos/api/cart.php',
        data: {
          product_id:id,
          custom_name: name,
          custom_price: price,
          custom_product: 'true',
          quantity:1
        }
      }).done(function(data){
        console.log(data);
        vueInstance.refresh();
      }).always(function(data, status, xhr){
        vueInstance.enablePos();
      });
    },
    deleteProduct: function(cart_index){
      this.disablePos();

      let cart_key = this.cartdata[cart_index].key;
      this.$delete(this.cartdata, cart_index);

      let vueInstance = this;

      jQuery.ajax({
        method:"DELETE",
        url:'https://igniteglass.com/pos/api/cart.php?' + jQuery.param({'cart_key':cart_key})
      }).done(function(data, status, xhr){
        if (xhr.status != 200 && xhr.status != 204){
          vueInstance.refresh();
        } else {
          vueInstance.updateTotals();
        }
      }).fail(function(data, status, xhr){
        vueInstance.refresh();
      }).always(function(data, status, xhr){
        console.log(xhr.status);
        vueInstance.enablePos();
      });
    },
    updateProducts: function(){
      this.disablePos();
      let vueInstance = this;

      jQuery.ajax({
        method:"PATCH",
        url:'https://igniteglass.com/pos/api/cart.php?' + jQuery.param({cartdata:this.cartdata}),
        data: {
          cart:this.cartdata
        }
      }).done(function(data, status, xhr){

        if (xhr.status == 205){
          vueInstance.refresh();
        } else {
          vueInstance.updateTotals();
        }

      }).fail(function(data, status, xhr){
        vueInstance.refresh();
      }).always(function(data, status, xhr){
        console.log(xhr.status);
        vueInstance.enablePos();
      });
    },
    getCoupons: function(){
      let vueInstance = this;

      jQuery.ajax({
        url:'https://igniteglass.com/pos/api/coupon.php'
      }).done(function(data, status, xhr){
        vueInstance.coupons = data;
      });
    },
    addCoupon: function(code){
      this.disablePos();
      let vueInstance = this;

      jQuery.ajax({
        method:"POST",
        url:'https://igniteglass.com/pos/api/coupon.php',
        data: {
          coupon_code:code
        }
      }).done(function(data){
        vueInstance.coupon_code = '';
        vueInstance.coupons = data;
        vueInstance.updateTotals();
      }).always(function(data, status, xhr){
        vueInstance.enablePos();
      });
    },
    deleteCoupon: function(code){
      this.disablePos();
      this.$delete(this.coupons, code);
      let vueInstance = this;

      jQuery.ajax({
        method:"DELETE",
        url:'https://igniteglass.com/pos/api/coupon.php?' + jQuery.param({'coupon_code':code})
      }).done(function(data, status, xhr){
        if (xhr.status != 200 && xhr.status != 204){
          vueInstance.getCoupons();
        }
        vueInstance.updateTotals();
      }).fail(function(data, status, xhr){
        vueInstance.getCoupons();
      }).always(function(data, status, xhr){
        vueInstance.enablePos();
      });

    },
    updateTotals: function(){

      let vueInstance = this;

      jQuery.ajax({
        url:'https://igniteglass.com/pos/api/totals.php'
      }).done(function(data, status, xhr){
        vueInstance.totals = data;
      });

    },
    getProducts: function(){
      let vueInstance = this;

      jQuery.ajax({
        url:'https://igniteglass.com/pos/api/products.php'
      }).done(function(data, status, xhr){
        vueInstance.product_list = data;
        vueInstance.filterProducts();
        //console.log(data);
      });
    },
    filterProducts: function(data=''){
      if (data == ''){
        this.product_view = this.product_list;
        return;
      }

      let pSearch = data.toLowerCase().trim().split(' ');
      product_view = [];
      for (p = 0; p<this.product_list.length; ++p){
        include = true;

        for (s = 0; s < pSearch.length; ++s){

          if ( this.product_list[p].name.toLowerCase().indexOf(pSearch[s]) < 0 ){
            include = false;
          }
        }

        if (include){
          product_view.push(this.product_list[p]);
        }
      }

      this.product_view = product_view;
    },
    getShippingMethods: function(){
      let vueInstance = this;

      jQuery.ajax({
        url:'https://igniteglass.com/pos/api/shipping.php'
      }).done(function(data, status, xhr){
        vueInstance.shipping_methods = data;
      });

    },
    updateShippingMethod:function(data){
      this.disablePos();

      let vueInstance = this;

      jQuery.ajax({
        method:"POST",
        url:'https://igniteglass.com/pos/api/shipping.php',
        data: {
          shipping_method:data
        }
      }).done(function(data){
        console.log(data);
        vueInstance.updateTotals();

      }).always(function(data, status, xhr){
        vueInstance.enablePos();
      });
    }
  }
});
