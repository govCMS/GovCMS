ARG CLI_IMAGE
FROM ${CLI_IMAGE} as cli

FROM amazeeio/solr:6.6-drupal
COPY --from=cli /app/web/modules/contrib/search_api_solr/solr-conf/6.x/ /solr-conf/conf/

USER root
RUN sed -i -e "s#<dataDir>\${solr.data.dir:}#<dataDir>/var/solr/\${solr.core.name}#g" /solr-conf/conf/solrconfig.xml \
    && sed -i -e "s#solr.lock.type:native#solr.lock.type:none#g" /solr-conf/conf/solrconfig.xml

USER solr
RUN precreate-core drupal /solr-conf
CMD ["solr-foreground"]
