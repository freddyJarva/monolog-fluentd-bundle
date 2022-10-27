## Configuration

These are the default parameters:

    monolog_fluentd:
      host: localhost
      port: 24224
      options: []
      level: debug
      tag_fmt: '{{channel}}.{{level_name}}'
      exceptions: true

You can modify them in config.yml or parameters.yml

You may load the handler as a service

    monolog:
      handlers:
        monolog_fluentd:
          type: service
          id: monolog_fluentd.fluentd_handler
